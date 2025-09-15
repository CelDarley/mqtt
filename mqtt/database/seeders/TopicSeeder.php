<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Topic;
use App\Models\DeviceType;
use App\Models\Department;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar alguns departamentos para criar tópicos
        $departments = Department::all();
        $deviceTypes = DeviceType::all();
        
        if ($departments->isEmpty()) {
            $this->command->error('❌ Nenhum departamento encontrado. Execute DepartmentSeeder primeiro.');
            return;
        }

        // Gerar MACs fictícios para os dispositivos
        $fakeMacs = [
            '8E:C3:7C:55:91:EB',
            'AA:BB:CC:DD:EE:FF',
            '12:34:56:78:9A:BC',
            'FE:DC:BA:98:76:54',
            '11:22:33:44:55:66',
            '77:88:99:AA:BB:CC',
            'DE:AD:BE:EF:CA:FE',
            'AB:CD:EF:12:34:56'
        ];

        $topics = [];

        // Para cada departamento, criar tópicos baseados nos tipos de dispositivos
        foreach ($departments as $department) {
            $departmentSlug = strtolower(str_replace([' ', 'ã', 'ç', 'á', 'é', 'í', 'ó', 'ú'], ['_', 'a', 'c', 'a', 'e', 'i', 'o', 'u'], $department->name));
            
            // Selecionar alguns tipos de dispositivos aleatoriamente para este departamento
            $selectedDeviceTypes = $deviceTypes->random(min(4, $deviceTypes->count()));
            
            foreach ($selectedDeviceTypes as $index => $deviceType) {
                $mac = $fakeMacs[$index % count($fakeMacs)];
                $deviceSlug = strtolower(str_replace([' ', 'ã', 'ç', 'á', 'é', 'í', 'ó', 'ú'], ['_', 'a', 'c', 'a', 'e', 'i', 'o', 'u'], $deviceType->name));
                
                // Criar tópico baseado no padrão: iot/{departamento}/{tipo_dispositivo}/{mac_address}
                $topicName = "iot/{$departmentSlug}/{$deviceSlug}/" . str_replace(':', '', $mac);
                
                $topics[] = [
                    'name' => $topicName,
                    'description' => "Tópico para {$deviceType->name} no departamento {$department->name} (MAC: {$mac})",
                    'is_active' => rand(0, 10) > 1, // 90% dos tópicos ativos
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(0, 5)),
                ];
            }
        }

        // Adicionar alguns tópicos de sistema
        $systemTopics = [
            [
                'name' => 'system/heartbeat',
                'description' => 'Tópico para monitoramento de conectividade do sistema',
                'is_active' => true,
                'created_at' => now()->subDays(30),
                'updated_at' => now(),
            ],
            [
                'name' => 'system/alerts',
                'description' => 'Tópico para alertas e notificações do sistema',
                'is_active' => true,
                'created_at' => now()->subDays(25),
                'updated_at' => now(),
            ],
            [
                'name' => 'system/status',
                'description' => 'Tópico para status geral do sistema IoT',
                'is_active' => true,
                'created_at' => now()->subDays(20),
                'updated_at' => now(),
            ],
            [
                'name' => 'system/config',
                'description' => 'Tópico para configurações do sistema',
                'is_active' => true,
                'created_at' => now()->subDays(15),
                'updated_at' => now(),
            ],
        ];

        $topics = array_merge($topics, $systemTopics);

        // Adicionar alguns tópicos de broadcast
        $broadcastTopics = [
            [
                'name' => 'broadcast/maintenance',
                'description' => 'Tópico para comandos de manutenção em broadcast',
                'is_active' => true,
                'created_at' => now()->subDays(10),
                'updated_at' => now(),
            ],
            [
                'name' => 'broadcast/emergency',
                'description' => 'Tópico para comandos de emergência em broadcast',
                'is_active' => true,
                'created_at' => now()->subDays(8),
                'updated_at' => now(),
            ],
            [
                'name' => 'broadcast/shift_change',
                'description' => 'Tópico para notificações de mudança de turno',
                'is_active' => true,
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ],
        ];

        $topics = array_merge($topics, $broadcastTopics);

        // Adicionar alguns tópicos específicos por tipo de sensor
        if ($deviceTypes->where('name', 'Sensor de Temperatura')->first()) {
            $tempTopics = [
                [
                    'name' => 'sensors/temperature/zone_a/average',
                    'description' => 'Média de temperatura da Zona A',
                    'is_active' => true,
                    'created_at' => now()->subDays(7),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'sensors/temperature/zone_b/average',
                    'description' => 'Média de temperatura da Zona B',
                    'is_active' => true,
                    'created_at' => now()->subDays(6),
                    'updated_at' => now(),
                ],
            ];
            $topics = array_merge($topics, $tempTopics);
        }

        if ($deviceTypes->where('name', 'LED de Controle')->first()) {
            $ledTopics = [
                [
                    'name' => 'actuators/leds/production_line/status',
                    'description' => 'Status dos LEDs da linha de produção',
                    'is_active' => true,
                    'created_at' => now()->subDays(4),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'actuators/leds/emergency/control',
                    'description' => 'Controle dos LEDs de emergência',
                    'is_active' => true,
                    'created_at' => now()->subDays(3),
                    'updated_at' => now(),
                ],
            ];
            $topics = array_merge($topics, $ledTopics);
        }

        // Inserir todos os tópicos
        foreach ($topics as $topic) {
            Topic::create($topic);
        }

        $this->command->info("✅ Topics seeded successfully! Created " . count($topics) . " topics.");
    }
}
