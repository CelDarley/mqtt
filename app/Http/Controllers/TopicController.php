<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use PhpMqtt\Client\MqttClient;

class TopicController extends Controller
{
    /**
     * Criar um novo tópico
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:topics,name',
            'description' => 'nullable|string',
            'group_id' => 'nullable|exists:device_groups,id'
        ]);

        $topic = Topic::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true
        ]);

        // Se um grupo foi especificado, criar a associação
        if ($request->group_id) {
            \App\Models\DeviceGroupAssignment::create([
                'device_id' => $topic->id,
                'group_id' => $request->group_id,
                'is_active' => true
            ]);
        }

        // Carregar o tópico com informações do grupo
        $topic->load('groupAssignment.group');

        return response()->json([
            'success' => true,
            'message' => 'Tópico criado com sucesso',
            'data' => $topic
        ], 201);
    }

    /**
     * Enviar mensagem para um tópico MQTT
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'topico' => 'required|string',
            'mensagem' => 'required|string'
        ]);

        $topicName = $request->topico;
        $message = $request->mensagem;

        // Verificar se o tópico existe no banco de dados
        $topic = Topic::where('name', $topicName)->where('is_active', true)->first();

        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Tópico não existe ou está inativo'
            ], 404);
        }

        try {
            // Criar cliente MQTT
            $client = new MqttClient(
                config('mqtt.host', env('MQTT_HOST', 'localhost')),
                config('mqtt.port', env('MQTT_PORT', 1883)),
                config('mqtt.client_id', env('MQTT_CLIENT_ID', 'laravel_mqtt_client'))
            );

            // Conectar ao broker MQTT
            $client->connect();

            // Publicar mensagem no tópico
            $client->publish($topicName, $message, 0);

            // Desconectar do broker
            $client->disconnect();

            return response()->json([
                'success' => true,
                'message' => 'Mensagem enviada com sucesso para o tópico: ' . $topicName,
                'data' => [
                    'topic' => $topicName,
                    'message' => $message
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagem MQTT: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos os tópicos
     */
    public function index(): JsonResponse
    {
        $topics = Topic::with('groupAssignment.group')
                      ->where('is_active', true)
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $topics
        ], 200);
    }

    /**
     * Mostrar um tópico específico
     */
    public function show($id): JsonResponse
    {
        $topic = Topic::find($id);

        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Tópico não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $topic
        ], 200);
    }

    /**
     * Desativar um tópico
     */
    public function deactivate($id): JsonResponse
    {
        $topic = Topic::find($id);

        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Tópico não encontrado'
            ], 404);
        }

        $topic->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Tópico desativado com sucesso'
        ], 200);
    }
}
