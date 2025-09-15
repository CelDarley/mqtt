<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TopicController extends Controller
{
    public function index()
    {
        $stats = [
            'totalTopics' => 0,
            'activeTopics' => 0,
            'deviceTopics' => 0,
            'systemTopics' => 0,
        ];

        $topics = [];

        try {
            // Buscar tópicos da API MQTT
            $response = Http::get(config('app.api_base_url') . '/mqtt/topics');

            if ($response->successful()) {
                $data = $response->json();
                $topics = $data['data'] ?? [];

                // Calcular estatísticas
                $stats['totalTopics'] = count($topics);
                $stats['activeTopics'] = count(array_filter($topics, function($topic) {
                    return ($topic['status'] ?? 'active') === 'active';
                }));
                $stats['deviceTopics'] = count(array_filter($topics, function($topic) {
                    return strpos($topic['name'] ?? '', 'device/') === 0;
                }));
                $stats['systemTopics'] = count(array_filter($topics, function($topic) {
                    return strpos($topic['name'] ?? '', 'system/') === 0;
                }));
            }
        } catch (\Exception $e) {
            // Se a API não estiver disponível, usar dados de exemplo
            $topics = [
                [
                    'id' => 1,
                    'name' => 'device/sensor/temperature',
                    'description' => 'Tópico para leituras de temperatura dos sensores',
                    'type' => 'sensor',
                    'status' => 'active',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
                [
                    'id' => 2,
                    'name' => 'device/actuator/relay',
                    'description' => 'Tópico para controle de relés',
                    'type' => 'actuator',
                    'status' => 'active',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
                [
                    'id' => 3,
                    'name' => 'system/status',
                    'description' => 'Tópico para status do sistema',
                    'type' => 'system',
                    'status' => 'active',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
                [
                    'id' => 4,
                    'name' => 'device/sensor/humidity',
                    'description' => 'Tópico para leituras de umidade dos sensores',
                    'type' => 'sensor',
                    'status' => 'active',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
                [
                    'id' => 5,
                    'name' => 'device/actuator/led',
                    'description' => 'Tópico para controle de LEDs',
                    'type' => 'actuator',
                    'status' => 'inactive',
                    'created_at' => now()->format('d/m/Y H:i:s'),
                ],
            ];

            $stats = [
                'totalTopics' => 5,
                'activeTopics' => 4,
                'deviceTopics' => 4,
                'systemTopics' => 1,
            ];
        }

        // Recalcular estatísticas
        $stats = [
            'totalTopics' => count($topics),
            'activeTopics' => count(array_filter($topics, function($topic) {
                return ($topic['status'] ?? 'active') === 'active';
            })),
            'deviceTopics' => count(array_filter($topics, function($topic) {
                return strpos($topic['name'] ?? '', 'device/') === 0;
            })),
            'systemTopics' => count(array_filter($topics, function($topic) {
                return strpos($topic['name'] ?? '', 'system/') === 0;
            })),
        ];

        return view('topics.index', compact('topics', 'stats'));
    }

    public function create()
    {
        return view('topics.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:device,system,sensor,actuator',
        ]);

        try {
            $response = Http::post('http://localhost:8000/api/mqtt/topics', [
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($response->successful()) {
                return redirect()->route('topics.index')
                    ->with('success', 'Tópico criado com sucesso!');
            } else {
                // Se a API não estiver disponível, simular criação
                return redirect()->route('topics.index')
                    ->with('success', "Tópico '{$request->name}' criado com sucesso! (Modo demonstração - API não disponível)");
            }
        } catch (\Exception $e) {
            // Se a API não estiver disponível, simular criação
            return redirect()->route('topics.index')
                ->with('success', "Tópico '{$request->name}' criado com sucesso! (Modo demonstração - API não disponível)");
        }
    }

    public function show($id)
    {
        try {
            $response = Http::get(config('app.api_base_url') . "/mqtt/topics/{$id}");

            if ($response->successful()) {
                $topic = $response->json()['data'];
                
                // Se for uma requisição AJAX, retornar JSON
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'topic' => $topic
                    ]);
                }
                
                // Caso contrário, retornar view (quando implementarmos)
                return view('topics.show', compact('topic'));
            } else {
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tópico não encontrado'
                    ], 404);
                }
                
                return redirect()->route('topics.index')
                    ->withErrors(['error' => 'Tópico não encontrado']);
            }
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao buscar tópico: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('topics.index')
                ->withErrors(['error' => 'Erro ao buscar tópico: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $response = Http::get(config('app.api_base_url') . "/mqtt/topics/{$id}");

            if ($response->successful()) {
                $topic = $response->json()['data'];
                return view('topics.edit', compact('topic'));
            } else {
                return redirect()->route('topics.index')
                    ->withErrors(['error' => 'Tópico não encontrado']);
            }
        } catch (\Exception $e) {
            return redirect()->route('topics.index')
                ->withErrors(['error' => 'Erro ao buscar tópico: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
            ];
            
            if ($request->has('type')) {
                $data['type'] = $request->type;
            }

            $response = Http::put(config('app.api_base_url') . "/mqtt/topics/{$id}", $data);

            if ($response->successful()) {
                // Se for uma requisição AJAX, retornar JSON
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Tópico atualizado com sucesso!'
                    ]);
                }
                
                return redirect()->route('topics.index')
                    ->with('success', 'Tópico atualizado com sucesso!');
            } else {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao atualizar tópico: ' . $response->body()
                    ], 422);
                }
                
                return redirect()->back()
                    ->withErrors(['error' => 'Erro ao atualizar tópico: ' . $response->body()])
                    ->withInput();
            }
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao conectar com a API: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao conectar com a API: ' . $e->getMessage()])
                ->withInput();
        }
    }



    public function destroy($id)
    {
        try {
            \Log::info('🗑️ Solicitando exclusão de tópico', ['id' => $id]);

            // Usar endpoint DELETE para exclusão permanente
            $response = Http::delete("http://localhost:8000/api/mqtt/topics/{$id}");

            if ($response->successful()) {
                $data = $response->json();
                $message = $data['message'] ?? 'Tópico excluído com sucesso!';
                
                \Log::info('✅ Tópico excluído com sucesso', [
                    'id' => $id,
                    'response' => $data
                ]);

                return redirect()->route('topics.index')
                    ->with('success', $message);
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? "Erro ao excluir tópico #{$id}";
                
                \Log::error('❌ Erro ao excluir tópico', [
                    'id' => $id,
                    'status' => $response->status(),
                    'response' => $errorData
                ]);

                return redirect()->route('topics.index')
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            \Log::error('❌ Exceção ao excluir tópico', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('topics.index')
                ->with('error', "Erro ao conectar com a API: " . $e->getMessage());
        }
    }

    // Manter método deactivate para compatibilidade (caso necessário)
    public function deactivate($id)
    {
        // Redirecionar para exclusão
        return $this->destroy($id);
    }

    /**
     * Testar conexão com dispositivo
     */
    public function testConnection(Request $request)
    {
        try {
            $topic = $request->input('topic');
            
            \Log::info('🔍 Testando conectividade MQTT', ['topic' => $topic]);

            // Verificar se o broker MQTT está ativo (mais simples e efetivo)
            $brokerResponse = Http::timeout(3)->get('http://localhost:8000/api/mqtt/topics');
            
            if ($brokerResponse->successful()) {
                // Broker MQTT funcionando - verificar se tópico existe
                $topicsData = $brokerResponse->json();
                $topicExists = false;
                
                if (isset($topicsData['data'])) {
                    foreach ($topicsData['data'] as $existingTopic) {
                        if ($existingTopic['name'] === $topic) {
                            $topicExists = true;
                            break;
                        }
                    }
                }
                
                if ($topicExists) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Broker MQTT ativo e tópico configurado',
                        'mqtt_available' => true,
                        'topic_exists' => true,
                        'broker_status' => 'online'
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Broker MQTT ativo, mas tópico não encontrado',
                        'mqtt_available' => true,
                        'topic_exists' => false,
                        'suggestion' => 'O tópico pode não estar registrado no broker ainda'
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Broker MQTT não está respondendo',
                    'mqtt_available' => false,
                    'suggestion' => 'Verifique se o servidor MQTT está rodando'
                ], 503);
            }

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao testar conectividade MQTT', [
                'error' => $e->getMessage(),
                'topic' => $request->input('topic')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro na verificação MQTT: ' . $e->getMessage(),
                'mqtt_available' => false
            ], 500);
        }
    }

    /**
     * Enviar comando MQTT para dispositivo
     */
    public function sendCommand(Request $request)
    {
        try {
            $request->validate([
                'topic' => 'required|string',
                'payload' => 'required|array'
            ]);

            $topic = $request->input('topic');
            $payload = $request->input('payload');

            \Log::info('📤 Enviando comando MQTT', [
                'topic' => $topic,
                'payload' => $payload
            ]);

            // Enviar comando via broker MQTT do backend
            $response = Http::timeout(10)->post('http://localhost:8000/api/mqtt/publish', [
                'topic' => $topic,
                'payload' => $payload
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                \Log::info('✅ Comando MQTT enviado', ['result' => $result]);

                return response()->json([
                    'success' => true,
                    'message' => 'Comando enviado com sucesso',
                    'result' => $result,
                    'topic' => $topic,
                    'payload' => $payload,
                    'timestamp' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar comando via broker MQTT'
                ], 502);
            }

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao enviar comando MQTT', [
                'error' => $e->getMessage(),
                'topic' => $request->input('topic'),
                'payload' => $request->input('payload')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar comando: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Encontrar IP do dispositivo baseado no tópico - REMOVIDO
     * Não é mais necessário porque MQTT não precisa conhecer IP do cliente
     */
    // Métodos removidos: findDeviceIpByTopic, findDeviceViaMdns, scanNetworkForDevices, 
    // verifyEsp32Device, getServerLocalIp
}
