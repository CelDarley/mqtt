<?php

namespace App\Services;

use App\Models\DeviceType;
use App\Models\OtaUpdateLog;
use App\Models\Topic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtaService
{
    private $firmwareBaseUrl;
    private $mqttService;

    public function __construct()
    {
        $this->firmwareBaseUrl = config('app.firmware_base_url', 'http://firmware.iot.local');
        // TODO: Integrar com serviço MQTT quando disponível
        // $this->mqttService = app(MqttService::class);
    }

    /**
     * Iniciar atualização OTA para um tipo de dispositivo
     */
    public function triggerOtaUpdate(int $deviceTypeId, array $options = []): array
    {
        try {
            $deviceType = DeviceType::findOrFail($deviceTypeId);
            
            // Verificar se há um update ativo para este tipo
            $activeUpdate = OtaUpdateLog::byDeviceType($deviceTypeId)
                ->whereIn('status', ['initiated', 'in_progress'])
                ->first();
                
            if ($activeUpdate) {
                return [
                    'success' => false,
                    'message' => 'Já existe uma atualização OTA em andamento para este tipo de dispositivo',
                    'active_update_id' => $activeUpdate->id
                ];
            }

            // Verificar se há firmware disponível
            $firmwareInfo = $this->getFirmwareInfo($deviceType);
            if (!$firmwareInfo['available']) {
                return [
                    'success' => false,
                    'message' => 'Nenhum firmware disponível para este tipo de dispositivo',
                    'device_type' => $deviceType->name
                ];
            }

            // Buscar dispositivos deste tipo
            $devices = $this->getDevicesByType($deviceTypeId);
            if (empty($devices)) {
                return [
                    'success' => false,
                    'message' => 'Nenhum dispositivo encontrado para este tipo',
                    'device_type' => $deviceType->name
                ];
            }

            // Criar log de OTA
            $otaLog = OtaUpdateLog::create([
                'device_type_id' => $deviceTypeId,
                'firmware_version' => $firmwareInfo['version'],
                'devices_count' => count($devices),
                'status' => 'initiated',
                'firmware_url' => $firmwareInfo['firmware_url'],
                'checksum_md5' => $firmwareInfo['checksum'],
                'firmware_size_bytes' => $firmwareInfo['size_bytes'],
                'metadata' => [
                    'device_type_name' => $deviceType->name,
                    'initiated_by' => $options['user_id'] ?? 'system',
                    'force_update' => $options['force_update'] ?? false,
                    'changelog' => $firmwareInfo['changelog'] ?? []
                ]
            ]);

            // Publicar comandos MQTT
            $publishResults = $this->publishOtaCommands($devices, $deviceType, $firmwareInfo, $otaLog);

            // Atualizar status
            $otaLog->markAsStarted();

            return [
                'success' => true,
                'message' => 'Comandos OTA enviados com sucesso',
                'ota_log_id' => $otaLog->id,
                'devices_count' => count($devices),
                'firmware_version' => $firmwareInfo['version'],
                'publish_results' => $publishResults
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao iniciar OTA update', [
                'device_type_id' => $deviceTypeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erro interno ao iniciar atualização OTA: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar informações do firmware
     */
    public function getFirmwareInfo(DeviceType $deviceType): array
    {
        $deviceSlug = $this->getDeviceSlug($deviceType->name);
        $versionUrl = "{$this->firmwareBaseUrl}/firmware/{$deviceSlug}/latest/version.json";

        try {
            $response = Http::timeout(10)->get($versionUrl);
            
            if ($response->successful()) {
                $versionData = $response->json();
                
                return [
                    'available' => true,
                    'version' => $versionData['version'],
                    'firmware_url' => $versionData['firmware_url'],
                    'checksum_url' => $versionData['checksum_url'],
                    'checksum' => $this->getChecksum($deviceSlug),
                    'size_bytes' => $versionData['size_bytes'] ?? 0,
                    'changelog' => $versionData['changelog'] ?? [],
                    'force_update' => $versionData['force_update'] ?? false,
                    'min_version' => $versionData['min_version'] ?? '1.0.0'
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar informações do firmware', [
                'device_type' => $deviceType->name,
                'url' => $versionUrl,
                'error' => $e->getMessage()
            ]);
        }

        return ['available' => false];
    }

    /**
     * Buscar dispositivos por tipo
     */
    private function getDevicesByType(int $deviceTypeId): array
    {
        $deviceType = DeviceType::find($deviceTypeId);
        if (!$deviceType) {
            return [];
        }

        // Buscar tópicos relacionados a este tipo de dispositivo
        $relatedTopics = $deviceType->getRelatedTopics();
        
        $devices = [];
        foreach ($relatedTopics as $topic) {
            // Extrair informações do tópico
            // Formato esperado: iot/{departamento}/{tipo_dispositivo}/{mac_address}
            $topicParts = explode('/', $topic->name);
            
            if (count($topicParts) >= 4) {
                $macAddress = $topicParts[3];
                $department = $topicParts[1] ?? 'unknown';
                
                $devices[] = [
                    'mac_address' => $macAddress,
                    'topic_name' => $topic->name,
                    'department' => $department,
                    'device_type_id' => $deviceTypeId,
                    'is_active' => $topic->is_active
                ];
            }
        }

        return $devices;
    }

    /**
     * Publicar comandos MQTT para dispositivos
     */
    private function publishOtaCommands(array $devices, DeviceType $deviceType, array $firmwareInfo, OtaUpdateLog $otaLog): array
    {
        $results = [];
        $deviceSlug = $this->getDeviceSlug($deviceType->name);

        foreach ($devices as $device) {
            try {
                $otaPayload = [
                    'command' => 'ota_update',
                    'ota_id' => $otaLog->id,
                    'firmware_version' => $firmwareInfo['version'],
                    'firmware_url' => $firmwareInfo['firmware_url'],
                    'checksum_url' => $firmwareInfo['checksum_url'],
                    'checksum_md5' => $firmwareInfo['checksum'],
                    'size_bytes' => $firmwareInfo['size_bytes'],
                    'force_update' => $firmwareInfo['force_update'],
                    'device_id' => $device['mac_address'],
                    'device_type' => $deviceSlug,
                    'timestamp' => now()->toISOString(),
                    'timeout_minutes' => 30
                ];

                // Tópico específico para OTA
                $otaTopic = $device['topic_name'] . '/ota';

                // TODO: Publicar via MQTT quando serviço estiver disponível
                // $published = $this->mqttService->publish($otaTopic, json_encode($otaPayload));
                
                // Por enquanto, simular publicação
                $published = true;
                
                $results[$device['mac_address']] = [
                    'status' => $published ? 'sent' : 'failed',
                    'topic' => $otaTopic,
                    'payload_size' => strlen(json_encode($otaPayload))
                ];

                Log::info('Comando OTA enviado', [
                    'device' => $device['mac_address'],
                    'topic' => $otaTopic,
                    'firmware_version' => $firmwareInfo['version']
                ]);

            } catch (\Exception $e) {
                $results[$device['mac_address']] = [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];

                Log::error('Erro ao enviar comando OTA', [
                    'device' => $device['mac_address'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Processar feedback de dispositivo
     */
    public function processDeviceFeedback(int $otaLogId, string $deviceId, array $feedback): bool
    {
        try {
            $otaLog = OtaUpdateLog::find($otaLogId);
            if (!$otaLog) {
                return false;
            }

            $otaLog->addDeviceResult($deviceId, $feedback);

            // Verificar se todos os dispositivos responderam
            $this->checkUpdateCompletion($otaLog);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao processar feedback de dispositivo', [
                'ota_log_id' => $otaLogId,
                'device_id' => $deviceId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar se o update foi completado
     */
    private function checkUpdateCompletion(OtaUpdateLog $otaLog): void
    {
        $resultsCount = count($otaLog->device_results ?? []);
        
        if ($resultsCount >= $otaLog->devices_count) {
            $successCount = $otaLog->getSuccessfulDevicesCount();
            
            if ($successCount == $otaLog->devices_count) {
                $otaLog->markAsCompleted();
            } else {
                $otaLog->markAsFailed("Apenas {$successCount} de {$otaLog->devices_count} dispositivos atualizaram com sucesso");
            }
        }
    }

    /**
     * Buscar status de um update OTA
     */
    public function getOtaStatus(int $otaLogId): array
    {
        $otaLog = OtaUpdateLog::with('deviceType')->find($otaLogId);
        
        if (!$otaLog) {
            return ['success' => false, 'message' => 'Update OTA não encontrado'];
        }

        return [
            'success' => true,
            'data' => [
                'id' => $otaLog->id,
                'device_type' => $otaLog->deviceType->name,
                'firmware_version' => $otaLog->firmware_version,
                'status' => $otaLog->status,
                'devices_count' => $otaLog->devices_count,
                'successful_devices' => $otaLog->getSuccessfulDevicesCount(),
                'failed_devices' => $otaLog->getFailedDevicesCount(),
                'success_rate' => $otaLog->getSuccessRate(),
                'duration_minutes' => $otaLog->getDurationMinutes(),
                'started_at' => $otaLog->started_at,
                'completed_at' => $otaLog->completed_at,
                'error_message' => $otaLog->error_message,
                'device_results' => $otaLog->device_results
            ]
        ];
    }

    /**
     * Converter nome do tipo de dispositivo para slug
     */
    private function getDeviceSlug(string $deviceTypeName): string
    {
        return strtolower(str_replace(
            [' ', 'ã', 'ç', 'á', 'é', 'í', 'ó', 'ú'],
            ['_', 'a', 'c', 'a', 'e', 'i', 'o', 'u'],
            $deviceTypeName
        ));
    }

    /**
     * Buscar checksum do firmware
     */
    private function getChecksum(string $deviceSlug): ?string
    {
        $checksumUrl = "{$this->firmwareBaseUrl}/firmware/{$deviceSlug}/latest/checksum.md5";
        
        try {
            $response = Http::timeout(5)->get($checksumUrl);
            if ($response->successful()) {
                return trim($response->body());
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar checksum', [
                'device_slug' => $deviceSlug,
                'url' => $checksumUrl,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
} 