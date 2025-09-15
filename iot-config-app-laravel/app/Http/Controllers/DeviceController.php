<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function add(Request $request)
    {
        // Novo fluxo: sempre mostrar formulário de criação de tópico
        // Se vier do captive portal, terá device_name e mac_address como parâmetros
        
        $deviceName = $request->get('device_name', '');
        $macAddress = $request->get('mac_address', '');
        
        \Log::info('📱 Página de criação de tópico acessada', [
            'device_name' => $deviceName,
            'mac_address' => $macAddress
        ]);
        
        return view('device.add-topic', compact('deviceName', 'macAddress'));
    }

    public function saveTopic(Request $request)
    {
        try {
            \Log::info('💾 Salvando tópico MQTT', [
                'device_name' => $request->input('device_name'),
                'device_type' => $request->input('device_type'),
                'department' => $request->input('department')
            ]);

            // Validação dos dados
            $validated = $request->validate([
                'device_name' => 'required|string|max:255',
                'mac_address' => 'required|string|max:17',
                'device_type' => 'required|string|in:sensor,atuador,gateway,controlador',
                'department' => 'required|string|in:producao,qualidade,manutencao,administrativo',
                'description' => 'nullable|string|max:500'
            ]);

            // Preparar dados no formato esperado pelos métodos existentes
            $deviceData = [
                'name' => $validated['device_name'],
                'macAddress' => $validated['mac_address'],
                'deviceType' => $validated['device_type'],
                'department' => $validated['department'],
                'ipAddress' => $request->input('ip_address', 'N/A'),
                'ssid' => $request->input('ssid', 'N/A'),
                'connectedAt' => now()
            ];

            // Gerar nome do tópico MQTT
            $topicName = $this->generateTopicName($deviceData);
            
            \Log::info('🏷️ Nome do tópico gerado', ['topic_name' => $topicName]);

            // Preparar dados para o backend MQTT (formato simples)
            $topicData = [
                'name' => $topicName,
                'description' => $this->generateTopicDescription($deviceData)
            ];

            \Log::info('📡 Enviando dados para backend MQTT', $topicData);

            // Chamar API do backend MQTT existente (projeto mqtt)
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->timeout(15)->post('http://localhost:8000/api/mqtt/topics', $topicData);

            \Log::info('📨 Resposta do backend MQTT', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                $errorMessage = 'Erro na API do backend MQTT';
                if ($response->status() === 422) {
                    $errors = $response->json('errors') ?? [];
                    $errorMessage = 'Dados inválidos: ' . implode(', ', array_flatten($errors));
                }
                
                \Log::error('❌ ' . $errorMessage, [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                throw new \Exception($errorMessage);
            }

            $result = $response->json();
            
            \Log::info('✅ Tópico criado com sucesso', $result);

            // Notificar o Raspberry Pi sobre o tópico criado
            $this->notifyRaspberryPi($topicName, $validated);

            \Log::info('✅ Tópico criado com sucesso no projeto MQTT', [
                'topic_name' => $topicName,
                'device_name' => $validated['device_name'],
                'mac_address' => $validated['mac_address']
            ]);

            // Retornar sucesso em JSON para AJAX
            return response()->json([
                'success' => true,
                'message' => '🎉 Tópico MQTT criado com sucesso!',
                'data' => [
                    'topic_name' => $topicName,
                    'device_name' => $validated['device_name'],
                    'device_type' => $validated['device_type'],
                    'department' => $validated['department'],
                    'mac_address' => $validated['mac_address'],
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Erro de validação', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('❌ Erro ao criar tópico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar tópico: ' . $e->getMessage()
            ], 500);
        }
    }



    public function config(Request $request)
    {
        $ssid = $request->get('ssid', '');
        return view('device.config', compact('ssid'));
    }

    public function save(Request $request)
    {
        // NOVA ABORDAGEM: Apenas criar tópico MQTT baseado no MAC do localStorage
        
        if ($request->isJson()) {
            // Requisição AJAX JSON
            return $this->saveDeviceJson($request);
        }
        
        // Fallback para requisições form normais (se ainda necessário)
        $request->validate([
            'ssid' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|string|in:sensor,atuador,monitor,controlador',
            'department' => 'required|string|in:producao,qualidade,manutencao,administrativo',
        ]);

        try {
            // 1. TESTAR CONECTIVIDADE PRIMEIRO
            \Log::info('Testando conectividade com o dispositivo IoT...');
            
            $testResponse = Http::timeout(5)->get('http://192.168.4.1:5000/api/status');
            
            if (!$testResponse->successful()) {
                \Log::error('Dispositivo IoT não está acessível: ' . $testResponse->status());
                throw new \Exception('Dispositivo IoT não está acessível. Verifique se está conectado à rede IOT-Zontec e se o dispositivo está ligado.');
            }
            
            \Log::info('Dispositivo IoT acessível, enviando credenciais WiFi...');
            
            // 2. ENVIAR CREDENCIAIS WIFI PARA O RASPBERRY PI
            $wifiResponse = Http::timeout(30)->post('http://192.168.4.1:5000/api/connect', [
                'ssid' => $request->ssid,
                'password' => $request->password,
            ]);

            \Log::info('Resposta do WiFi:', ['status' => $wifiResponse->status(), 'body' => $wifiResponse->body()]);

            if (!$wifiResponse->successful()) {
                $errorBody = $wifiResponse->json();
                $errorMessage = $errorBody['message'] ?? 'Erro desconhecido';
                \Log::error('Falha ao conectar WiFi: ' . $errorMessage);
                throw new \Exception('Falha ao conectar o dispositivo à rede WiFi: ' . $errorMessage);
            }

            $wifiData = $wifiResponse->json();
            
            if (!$wifiData['success']) {
                \Log::error('Dispositivo falhou ao conectar: ' . ($wifiData['message'] ?? 'Erro desconhecido'));
                throw new \Exception('Dispositivo não conseguiu conectar à rede: ' . ($wifiData['message'] ?? 'Erro desconhecido'));
            }

            \Log::info('WiFi conectado com sucesso', $wifiData);

            // 3. OBTER MAC ADDRESS REAL DO DISPOSITIVO
            $realMacAddress = $wifiData['device_info']['mac_address'] ?? null;
            $deviceIpAddress = $wifiData['device_info']['ip_address'] ?? null;
            
            if (!$realMacAddress) {
                \Log::info('MAC address não retornado, tentando via API de status...');
                // Fallback: tentar obter via API de status
                $statusResponse = Http::timeout(10)->get('http://192.168.4.1:5000/api/status');
                if ($statusResponse->successful()) {
                    $statusData = $statusResponse->json();
                    $realMacAddress = $statusData['mac_address'] ?? $this->generateMacAddress();
                    $deviceIpAddress = $statusData['ip_address'] ?? null;
                    \Log::info('MAC obtido via status API: ' . $realMacAddress);
                } else {
                    $realMacAddress = $this->generateMacAddress();
                    \Log::warning('Usando MAC gerado: ' . $realMacAddress);
                }
            }
            
            // 4. PREPARAR DADOS DO DISPOSITIVO
            $deviceData = [
                'name' => $request->device_name,
                'macAddress' => $realMacAddress,
                'ipAddress' => $deviceIpAddress,
                'deviceType' => $request->device_type,
                'department' => $request->department,
                'ssid' => $request->ssid,
                'connectedAt' => now(),
            ];

            \Log::info('Dados do dispositivo preparados', $deviceData);

            // 5. CRIAR TÓPICO MQTT
            $response = Http::timeout(15)->post('http://localhost:8000/api/mqtt/topics', [
                'name' => $this->generateTopicName($deviceData),
                'description' => $this->generateTopicDescription($deviceData),
            ]);

            \Log::info('Resposta MQTT:', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                $result = $response->json();
                
                \Log::info('Configuração completada com sucesso');
                
                return redirect()->back()->with('api_result', [
                    'success' => true,
                    'message' => 'Dispositivo conectado com sucesso!',
                    'device_info' => [
                        'name' => $request->device_name,
                        'mac_address' => $realMacAddress,
                        'ip_address' => $deviceIpAddress,
                        'ssid' => $request->ssid,
                        'type' => $request->device_type,
                        'department' => $request->department,
                    ],
                    'mqtt_info' => [
                        'topic' => $result['data']['name'] ?? 'N/A',
                        'topic_id' => $result['data']['id'] ?? 'N/A',
                    ],
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                ]);
            } else {
                throw new \Exception('Erro ao criar tópico MQTT: ' . $response->body());
            }

        } catch (\Exception $e) {
            \Log::error('Erro na configuração do dispositivo: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao configurar dispositivo: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    /**
     * Nova abordagem: Salvar dispositivo via JSON (AJAX)
     */
    private function saveDeviceJson(Request $request)
    {
        try {
            \Log::info('📱 Nova abordagem - Salvando dispositivo via JSON', [
                'data' => $request->all()
            ]);

            // Validação dos dados
            $validated = $request->validate([
                'mac_address' => 'required|string|max:17',
                'device_name' => 'required|string|max:255',
                'device_type' => 'required|string|in:sensor,atuador,monitor,controlador',
                'department' => 'required|string|in:producao,qualidade,manutencao,administrativo',
            ]);

            // Preparar dados do dispositivo
            $deviceData = [
                'name' => $validated['device_name'],
                'macAddress' => $validated['mac_address'],
                'ipAddress' => 'N/A', // Será atualizado quando ESP32 se conectar
                'deviceType' => $validated['device_type'],
                'department' => $validated['department'],
                'ssid' => 'N/A', // Já foi configurado anteriormente
                'connectedAt' => now(),
            ];

            // Gerar nome do tópico MQTT
            $topicName = $this->generateTopicName($deviceData);
            
            \Log::info('🏷️ Nome do tópico gerado', ['topic_name' => $topicName]);

            // Criar tópico MQTT no backend
            $response = Http::timeout(15)->post('http://localhost:8000/api/mqtt/topics', [
                'name' => $topicName,
                'description' => $this->generateTopicDescription($deviceData),
            ]);

            \Log::info('📨 Resposta do backend MQTT', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                $errorMessage = 'Erro na API do backend MQTT';
                if ($response->status() === 422) {
                    $errors = $response->json('errors') ?? [];
                    $errorMessage = 'Dados inválidos: ' . implode(', ', array_flatten($errors));
                }
                
                \Log::error('❌ ' . $errorMessage, [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                throw new \Exception($errorMessage);
            }

            $result = $response->json();
            
            \Log::info('✅ Tópico criado com sucesso', $result);

            // Retornar sucesso em JSON
            return response()->json([
                'success' => true,
                'message' => '🎉 Tópico MQTT criado com sucesso!',
                'mqtt_info' => [
                    'topic' => $topicName,
                    'broker' => '192.168.0.106', // IP do broker MQTT
                    'port' => 1883,
                    'topic_id' => $result['data']['id'] ?? null,
                    'timestamp' => now()->toISOString()
                ],
                'device_info' => [
                    'name' => $validated['device_name'],
                    'mac_address' => $validated['mac_address'],
                    'type' => $validated['device_type'],
                    'department' => $validated['department']
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Erro de validação', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('❌ Erro ao criar tópico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar tópico: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar dispositivo via API (chamada do próprio dispositivo)
     */
    public function registerDevice(Request $request)
    {
        try {
            \Log::info('📡 API registerDevice chamada', ['data' => $request->all()]);
            
            // Validar dados recebidos
            $validated = $request->validate([
                'device_name' => 'required|string|max:255',
                'device_type' => 'required|string|in:sensor,atuador,gateway,controlador',
                'department' => 'required|string|in:producao,qualidade,manutencao,administrativo',
                'ssid' => 'required|string|max:255',
                'mac_address' => 'required|string|max:17',
                'ip_address' => 'required|string|max:15'
            ]);
            
            // Gerar dados do tópico MQTT
            $topicData = [
                'name' => $validated['device_name'],
                'deviceType' => $validated['device_type'],
                'department' => $validated['department'],
                'macAddress' => $validated['mac_address'],
                'ipAddress' => $validated['ip_address'],
                'ssid' => $validated['ssid'],
                'connectedAt' => now()
            ];
            
            $topicName = $this->generateTopicName($topicData);
            $topicDescription = $this->generateTopicDescription($topicData);
            
            \Log::info('🏷️ Gerando tópico MQTT', [
                'topic_name' => $topicName,
                'description' => $topicDescription
            ]);
            
            // Tentar criar tópico no backend MQTT
            try {
                $mqttResponse = Http::timeout(10)->post('http://localhost:8000/api/mqtt/topics', [
                    'name' => $topicName,
                    'description' => $topicDescription
                ]);
                
                if ($mqttResponse->successful()) {
                    $mqttData = $mqttResponse->json();
                    \Log::info('✅ Tópico MQTT criado', ['mqtt_data' => $mqttData]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Dispositivo registrado com sucesso',
                        'device_info' => [
                            'name' => $validated['device_name'],
                            'mac_address' => $validated['mac_address'],
                            'ip_address' => $validated['ip_address'],
                            'ssid' => $validated['ssid'],
                            'type' => $validated['device_type'],
                            'department' => $validated['department']
                        ],
                        'mqtt_info' => [
                            'topic' => $topicName,
                            'topic_id' => $mqttData['data']['id'] ?? null,
                            'timestamp' => now()->toISOString()
                        ]
                    ]);
                } else {
                    \Log::error('❌ Erro ao criar tópico MQTT', [
                        'status' => $mqttResponse->status(),
                        'response' => $mqttResponse->body()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao criar tópico MQTT: ' . $mqttResponse->body()
                    ], 500);
                }
                
            } catch (\Exception $e) {
                \Log::error('❌ Exceção ao chamar API MQTT', ['error' => $e->getMessage()]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de conexão com o backend MQTT: ' . $e->getMessage()
                ], 500);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('⚠️ Dados inválidos na API registerDevice', ['errors' => $e->errors()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('❌ Erro interno na API registerDevice', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir página de sucesso após configuração de dispositivo
     */
    public function success()
    {
        return view('device.success');
    }

    /**
     * Exibir página de transição de rede com formulário do app
     */
    public function transition(Request $request)
    {
        $macAddress = $request->get('mac');
        $ssid = $request->get('ssid');
        
        return view('device.transition', compact('macAddress', 'ssid'));
    }

        /**
     * API: Retornar tipos de dispositivos disponíveis
     */
    public function getDeviceTypes()
    {
        try {
            // Buscar tipos de dispositivo da API principal
            $apiUrl = config('app.api_base_url', 'http://localhost:8000/api');
            $response = Http::timeout(10)->get($apiUrl . '/device-types', [
                'active_only' => true
            ]);

            if ($response->successful()) {
                $deviceTypes = $response->json()['data'] ?? [];
                
                // Converter para o formato esperado pelo frontend
                $formattedTypes = array_map(function($type) {
                    return [
                        'value' => $type['name'],
                        'label' => ($type['icon'] ?? '📱') . ' ' . $type['name']
                    ];
                }, $deviceTypes);

                return response()->json([
                    'success' => true,
                    'data' => $formattedTypes
                ]);
            }

            // Fallback para tipos fixos se a API falhar
            \Log::warning('API de tipos de dispositivo falhou, usando fallback');
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar tipos de dispositivo: ' . $e->getMessage());
        }

        // Fallback para tipos fixos
        $deviceTypes = [
            ['value' => 'sensor', 'label' => '📊 Sensor'],
            ['value' => 'atuador', 'label' => '⚡ Atuador'],
            ['value' => 'gateway', 'label' => '🌐 Gateway'],
            ['value' => 'controlador', 'label' => '🎛️ Controlador']
        ];

        return response()->json([
            'success' => true,
            'data' => $deviceTypes
        ]);
    }

    /**
     * API: Retornar departamentos disponíveis
     */
    public function getDepartments()
    {
        try {
            // Buscar departamentos da API principal
            $apiUrl = config('app.api_base_url', 'http://localhost:8000/api');
            $response = Http::timeout(10)->get($apiUrl . '/departments');

            if ($response->successful()) {
                $departments = $response->json()['data'] ?? [];
                
                // Converter para o formato esperado pelo frontend
                $formattedDepts = array_map(function($dept) {
                    return [
                        'value' => strtolower(str_replace(' ', '_', $dept['name'])),
                        'label' => '🏢 ' . $dept['name']
                    ];
                }, $departments);

                return response()->json([
                    'success' => true,
                    'data' => $formattedDepts
                ]);
            }

            // Fallback para departamentos fixos se a API falhar
            \Log::warning('API de departamentos falhou, usando fallback');
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar departamentos: ' . $e->getMessage());
        }

        // Fallback para departamentos fixos
        $departments = [
            ['value' => 'producao', 'label' => '🏭 Produção'],
            ['value' => 'qualidade', 'label' => '✅ Qualidade'],
            ['value' => 'manutencao', 'label' => '🔧 Manutenção'],
            ['value' => 'administrativo', 'label' => '📋 Administrativo']
        ];

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    private function generateMacAddress()
    {
        return strtoupper(implode(':', array_map(function() {
            return sprintf('%02x', mt_rand(0, 255));
        }, range(0, 5))));
    }

    private function generateTopicName($deviceData)
    {
        $timestamp = time();
        $sanitizedDepartment = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $deviceData['department']));
        $sanitizedMac = str_replace(':', '', $deviceData['macAddress']);
        
        return "iot/{$sanitizedDepartment}/{$deviceData['deviceType']}/{$sanitizedMac}";
    }

    private function generateTopicDescription($deviceData)
    {
        return sprintf(
            "Dispositivo IoT: %s | Tipo: %s | Departamento: %s | MAC: %s | IP: %s | SSID: %s | Conectado em: %s",
            $deviceData['name'],
            $deviceData['deviceType'],
            $deviceData['department'],
            $deviceData['macAddress'],
            $deviceData['ipAddress'] ?? 'N/A',
            $deviceData['ssid'],
            $deviceData['connectedAt']->format('d/m/Y H:i:s')
        );
    }

    /**
     * Notificar o Raspberry Pi sobre tópico criado
     */
    private function notifyRaspberryPi($topicName, $validated)
    {
        try {
            \Log::info('📡 Notificando Raspberry Pi sobre tópico criado', [
                'topic' => $topicName,
                'device_mac' => $validated['mac_address']
            ]);

            // Determinar IP do Raspberry Pi baseado no MAC
            $raspberryIp = $this->getRaspberryPiIp($validated['mac_address']);
            
            if (!$raspberryIp) {
                \Log::warning('⚠️ IP do Raspberry Pi não encontrado para MAC: ' . $validated['mac_address']);
                return;
            }

            // Dados para enviar ao Raspberry Pi
            $topicData = [
                'name' => $topicName,
                'description' => $validated['description'] ?? '',
                'device_name' => $validated['device_name'],
                'device_type' => $validated['device_type'],
                'department' => $validated['department'],
                'device_mac' => $validated['mac_address'],
                'created_at' => now()->toISOString()
            ];

            // Enviar notificação via HTTP
            $raspberryUrl = "http://{$raspberryIp}:5000/api/mqtt/topic";
            
            \Log::info('📤 Enviando dados para Raspberry Pi', [
                'url' => $raspberryUrl,
                'data' => $topicData
            ]);

            $response = Http::timeout(10)->post($raspberryUrl, $topicData);

            if ($response->successful()) {
                \Log::info('✅ Raspberry Pi notificado com sucesso', [
                    'response' => $response->json()
                ]);
            } else {
                \Log::warning('⚠️ Falha ao notificar Raspberry Pi', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao notificar Raspberry Pi', [
                'error' => $e->getMessage(),
                'topic' => $topicName
            ]);
        }
    }

    /**
     * Determinar IP do Raspberry Pi baseado no MAC address
     */
    private function getRaspberryPiIp($macAddress)
    {
        try {
            // Mapeamento básico MAC -> IP (pode ser melhorado com descoberta automática)
            $knownDevices = [
                // Adicione aqui os MACs conhecidos dos Raspberry Pi
                // Exemplo: 'b8:27:eb:12:34:56' => '192.168.0.107'
            ];

            // Verificar se temos um IP conhecido para este MAC
            if (isset($knownDevices[$macAddress])) {
                return $knownDevices[$macAddress];
            }

            // Estratégia 1: Tentar IPs comuns da rede
            $commonIps = [
                '192.168.0.107',  // IP padrão configurado
                '192.168.1.107',
                '192.168.0.108',
                '192.168.1.108'
            ];

            foreach ($commonIps as $ip) {
                if ($this->pingRaspberryPi($ip)) {
                    \Log::info("📍 Raspberry Pi encontrado em: {$ip}");
                    return $ip;
                }
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao determinar IP do Raspberry Pi', [
                'error' => $e->getMessage(),
                'mac' => $macAddress
            ]);
            return null;
        }
    }

    /**
     * Verificar se Raspberry Pi está acessível no IP
     */
    private function pingRaspberryPi($ip)
    {
        try {
            $response = Http::timeout(3)->get("http://{$ip}:5000/api/mqtt/status");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}

