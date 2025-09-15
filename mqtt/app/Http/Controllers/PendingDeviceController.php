<?php

namespace App\Http\Controllers;

use App\Models\PendingDevice;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PendingDeviceController extends Controller
{
    public function __construct()
    {
        // Endpoints públicos para ESP32 e interface web
        $this->middleware('auth:api')->except(['store', 'index', 'show', 'findByMac', 'stats', 'activate', 'destroy', 'reject']);
    }

    /**
     * Lista dispositivos pendentes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PendingDevice::query()->with('activatedBy');

            // Filtrar por status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filtrar por período
            if ($request->has('period')) {
                switch ($request->period) {
                    case 'today':
                        $todayStart = \Carbon\Carbon::today()->timestamp * 1000;
                        $todayEnd = \Carbon\Carbon::tomorrow()->timestamp * 1000;
                        $query->whereBetween('registered_at', [$todayStart, $todayEnd]);
                        break;
                    case 'week':
                        $weekStart = \Carbon\Carbon::now()->subWeek()->timestamp * 1000;
                        $query->where('registered_at', '>=', $weekStart);
                        break;
                    case 'month':
                        $monthStart = \Carbon\Carbon::now()->subMonth()->timestamp * 1000;
                        $query->where('registered_at', '>=', $monthStart);
                        break;
                }
            }

            // Ordenação
            $query->orderBy('registered_at', 'desc');

            // Paginação ou lista completa
            if ($request->has('per_page')) {
                $devices = $query->paginate($request->per_page);
            } else {
                $devices = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $devices,
                'stats' => PendingDevice::getStats()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar dispositivos pendentes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Registrar novo dispositivo (endpoint público para ESP32)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'mac_address' => 'required|string|max:17',
                'device_name' => 'required|string|max:100',
                'ip_address' => 'nullable|ip',
                'wifi_ssid' => 'nullable|string|max:50',
                'device_info' => 'nullable|array',
                'device_info.firmware_version' => 'nullable|string',
                'device_info.esp32_model' => 'nullable|string',
                'device_info.free_heap' => 'nullable|integer'
            ]);

            // Verificar se já existe dispositivo com este MAC
            $existingDevice = PendingDevice::findByMac($validatedData['mac_address']);
            
            if ($existingDevice) {
                // Se já existe e está pendente, atualizar dados
                if ($existingDevice->status === 'pending') {
                    $existingDevice->update([
                        'device_name' => $validatedData['device_name'],
                        'ip_address' => $validatedData['ip_address'] ?? $existingDevice->ip_address,
                        'wifi_ssid' => $validatedData['wifi_ssid'] ?? $existingDevice->wifi_ssid,
                        'device_info' => array_merge($existingDevice->device_info ?? [], $validatedData['device_info'] ?? []),
                        'registered_at' => $validatedData['registered_at'] ?? time() * 1000
                    ]);

                    Log::info("Dispositivo pendente atualizado: {$validatedData['mac_address']}");
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Dispositivo atualizado com sucesso',
                        'data' => $existingDevice->fresh()
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dispositivo já registrado com status: ' . $existingDevice->formatted_status
                    ], 409);
                }
            }

            // Criar novo dispositivo
            $device = PendingDevice::createFromESP32($validatedData);
            
            Log::info("Novo dispositivo registrado: {$device->mac_address} - {$device->device_name}");
            
            return response()->json([
                'success' => true,
                'message' => 'Dispositivo registrado com sucesso',
                'data' => $device
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erro ao registrar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Mostrar dispositivo específico
     */
    public function show($id): JsonResponse
    {
        try {
            $device = PendingDevice::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $device
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo não encontrado'
            ], 404);
        }
    }

    /**
     * Ativar dispositivo (criar tópico MQTT)
     */
    public function activate(Request $request, $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'device_type' => 'required|string|in:sensor,atuador,controlador,monitor',
                'department' => 'required|string|max:50'
            ]);

            $device = PendingDevice::findOrFail($id);
            
            if (!$device->canBeActivated()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não pode ser ativado. Status atual: ' . $device->formatted_status
                ], 400);
            }

            // Criar tópico MQTT simples: iot/<mac_address>
            $macForTopic = str_replace(':', '', strtolower($device->mac_address));
            $topicName = "iot/{$macForTopic}";
            
            $topic = Topic::create([
                'name' => $topicName,
                'device_mac' => $device->mac_address,
                'device_name' => $device->device_name,
                'device_type' => $validatedData['device_type'],
                'department' => $validatedData['department'],
                'description' => "Tópico para {$device->device_name} ({$validatedData['device_type']}) - Departamento: {$validatedData['department']}",
                'created_by' => auth()->id(),
                'is_active' => true
            ]);

            // Ativar dispositivo
            $device->activate(auth()->id(), $validatedData['device_type'], $validatedData['department']);
            
            Log::info("Dispositivo ativado: {$device->mac_address} -> {$topicName}");

            // Enviar configuração MQTT para o dispositivo
            $mqttConfigSent = $this->sendMQTTConfigToDevice($device, $topic);

            return response()->json([
                'success' => true,
                'message' => '🎉 Tópico MQTT criado com sucesso!' . ($mqttConfigSent ? ' Configuração enviada ao dispositivo.' : ' Aguardando dispositivo para receber configuração.'),
                'data' => [
                    'topic_name' => $topicName,
                    'device_name' => $device->device_name,
                    'device_type' => $validatedData['device_type'],
                    'department' => $validatedData['department'],
                    'mac_address' => $device->mac_address,
                    'mqtt_config_sent' => $mqttConfigSent,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erro ao ativar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Rejeitar dispositivo
     */
    public function reject($id): JsonResponse
    {
        try {
            $device = PendingDevice::findOrFail($id);
            
            if ($device->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas dispositivos pendentes podem ser rejeitados'
                ], 400);
            }

            $device->reject(auth()->id());
            
            Log::info("Dispositivo rejeitado: {$device->mac_address}");
            
            return response()->json([
                'success' => true,
                'message' => 'Dispositivo rejeitado com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao rejeitar dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Excluir dispositivo
     */
    public function destroy($id): JsonResponse
    {
        try {
            $device = PendingDevice::findOrFail($id);
            $device->delete();
            
            Log::info("Dispositivo excluído: {$device->mac_address}");
            
            return response()->json([
                'success' => true,
                'message' => 'Dispositivo excluído com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir dispositivo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Buscar dispositivo por MAC address
     */
    public function findByMac(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'mac_address' => 'required|string|max:17'
            ]);

            $device = PendingDevice::findByMac($request->mac_address);
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $device
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'MAC address inválido',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dispositivo por MAC: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Estatísticas dos dispositivos
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = PendingDevice::getStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Enviar configuração MQTT para o dispositivo ESP32
     */
    private function sendMQTTConfigToDevice(PendingDevice $device, Topic $topic): bool
    {
        try {
            if (!$device->ip_address) {
                Log::warning("Dispositivo {$device->mac_address} não tem IP address");
                return false;
            }

            $mqttConfig = [
                'mqtt_broker' => env('MQTT_HOST', '10.102.0.101'),
                'mqtt_port' => (int) env('MQTT_PORT', 1883),
                'mqtt_topic' => $topic->name,
                'mqtt_user' => env('MQTT_USERNAME', ''),
                'mqtt_password' => env('MQTT_PASSWORD', '')
            ];

            $url = "http://{$device->ip_address}/mqtt-config";
            
            Log::info("Enviando configuração MQTT para {$device->ip_address}: " . json_encode($mqttConfig));

            $response = Http::timeout(10)->post($url, $mqttConfig);

            if ($response->successful()) {
                Log::info("Configuração MQTT enviada com sucesso para {$device->mac_address}");
                return true;
            } else {
                Log::warning("Falha ao enviar configuração MQTT para {$device->mac_address}: HTTP {$response->status()}");
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Erro ao enviar configuração MQTT para {$device->mac_address}: " . $e->getMessage());
            return false;
        }
    }
}
