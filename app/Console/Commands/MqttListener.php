<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use Illuminate\Support\Facades\Http;

class MqttListener extends Command
{
    protected $signature = 'mqtt:listen {--topic=iot/system/device-registration}';
    protected $description = 'Escutar registros automáticos de dispositivos ESP32 via MQTT';

    public function handle()
    {
        $topicPattern = $this->option('topic');
        
        $this->info("🚀 Iniciando listener MQTT para auto-registros...");
        $this->info("📡 Tópico: {$topicPattern}");
        
        try {
            // Criar cliente MQTT
            $client = new MqttClient(
                config('mqtt.host', env('MQTT_HOST', 'localhost')),
                config('mqtt.port', env('MQTT_PORT', 1883)),
                config('mqtt.client_id', env('MQTT_CLIENT_ID', 'laravel_listener'))
            );
            
            // Conectar ao broker MQTT
            $client->connect();
            $this->info("✅ Conectado ao broker MQTT");
            
            // Inscrever no tópico de registros
            $client->subscribe($topicPattern, function ($topic, $message) {
                $this->processDeviceRegistration($topic, $message);
            }, 0);
            
            $this->info("📻 Escutando registros de dispositivos...");
            $this->info("⏹️  Pressione Ctrl+C para parar");
            
            // Loop infinito para escutar mensagens
            while (true) {
                $client->loop(true);
                usleep(100000); // 100ms
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erro no listener MQTT: " . $e->getMessage());
            return 1;
        }
    }
    
    private function processDeviceRegistration($topic, $message)
    {
        try {
            $this->info("📱 Novo registro recebido via MQTT:");
            $this->line("📡 Tópico: {$topic}");
            
            $data = json_decode($message, true);
            
            if (!$data || !isset($data['action']) || $data['action'] !== 'device_registration') {
                $this->warn("⚠️  Mensagem ignorada - não é um registro de dispositivo");
                return;
            }
            
            $this->line("📊 Dados: " . json_encode($data, JSON_PRETTY_PRINT));
            
            // Enviar para API de processamento
            $response = Http::timeout(10)->post('http://localhost:8000/api/mqtt/device-registration', [
                'device_mac' => $data['device_mac'] ?? '',
                'device_ip' => $data['device_ip'] ?? '',
                'wifi_ssid' => $data['wifi_ssid'] ?? '',
                'device_type' => $data['device_type'] ?? 'esp32',
                'firmware_version' => $data['firmware_version'] ?? '',
                'registered_at' => $data['registered_at'] ?? time()
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $this->info("✅ Registro processado: " . $result['message']);
                
                if (isset($result['data']['topic_name'])) {
                    $this->line("🏷️  Tópico criado: " . $result['data']['topic_name']);
                }
            } else {
                $this->error("❌ Falha no processamento: " . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erro ao processar registro: " . $e->getMessage());
        }
        
        $this->line("─────────────────────────────────────");
    }
} 