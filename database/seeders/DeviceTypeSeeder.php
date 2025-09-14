<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeviceType;

class DeviceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deviceTypes = [
            [
                'name' => 'Sensor de Temperatura',
                'description' => 'Sensor para monitoramento de temperatura ambiente e de equipamentos',
                'icon' => '🌡️',
                'specifications' => [
                    'voltagem' => '3.3V',
                    'protocolo' => 'WiFi',
                    'range_temperatura' => '-40°C a +125°C',
                    'precisao' => '±0.5°C',
                    'interface' => 'I2C/SPI',
                    'consumo' => '2.5mA'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sensor de Umidade',
                'description' => 'Sensor para medição de umidade relativa do ar',
                'icon' => '💧',
                'specifications' => [
                    'voltagem' => '3.3V - 5V',
                    'protocolo' => 'WiFi',
                    'range_umidade' => '0% a 100% RH',
                    'precisao' => '±2% RH',
                    'tempo_resposta' => '8s',
                    'interface' => 'Digital'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'LED de Controle',
                'description' => 'LED para sinalização e controle visual de status',
                'icon' => '💡',
                'specifications' => [
                    'voltagem' => '12V/24V',
                    'corrente' => '20mA',
                    'cores' => 'RGB',
                    'controle' => 'PWM',
                    'durabilidade' => '50000h',
                    'protocolo' => 'WiFi/Bluetooth'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sensor de Movimento',
                'description' => 'Sensor PIR para detecção de movimento e presença',
                'icon' => '🚶',
                'specifications' => [
                    'voltagem' => '5V',
                    'alcance' => '7 metros',
                    'angulo_deteccao' => '120°',
                    'delay_time' => '5s - 300s',
                    'protocolo' => 'WiFi',
                    'consumo' => '65mA'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Relé de Controle',
                'description' => 'Relé para acionamento de equipamentos de alta potência',
                'icon' => '⚡',
                'specifications' => [
                    'voltagem_controle' => '3.3V',
                    'voltagem_carga' => '250V AC / 30V DC',
                    'corrente_max' => '10A',
                    'tipo' => 'SPDT',
                    'protocolo' => 'WiFi',
                    'isolacao' => 'Ótica'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sensor de Pressão',
                'description' => 'Sensor para monitoramento de pressão em sistemas pneumáticos/hidráulicos',
                'icon' => '🔧',
                'specifications' => [
                    'voltagem' => '5V',
                    'range_pressao' => '0-100 PSI',
                    'precisao' => '±0.25%',
                    'saida' => '4-20mA',
                    'protocolo' => 'Modbus/WiFi',
                    'material' => 'Aço inoxidável'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Câmera de Monitoramento',
                'description' => 'Câmera IP para vigilância e monitoramento industrial',
                'icon' => '📹',
                'specifications' => [
                    'resolucao' => '1080p Full HD',
                    'protocolo' => 'WiFi/Ethernet',
                    'visao_noturna' => 'IR até 20m',
                    'angulo_visao' => '90°',
                    'armazenamento' => 'SD Card / Cloud',
                    'alimentacao' => 'PoE / 12V'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sensor de Vibração',
                'description' => 'Sensor para monitoramento de vibração em máquinas e equipamentos',
                'icon' => '📳',
                'specifications' => [
                    'voltagem' => '3.3V',
                    'range_frequencia' => '0.5Hz - 1kHz',
                    'sensibilidade' => '100mV/g',
                    'range_temperatura' => '-40°C a +85°C',
                    'protocolo' => 'WiFi/LoRa',
                    'interface' => 'SPI'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Display OLED',
                'description' => 'Display para visualização de dados e status local',
                'icon' => '📺',
                'specifications' => [
                    'tamanho' => '0.96 polegadas',
                    'resolucao' => '128x64 pixels',
                    'voltagem' => '3.3V - 5V',
                    'interface' => 'I2C/SPI',
                    'cores' => 'Monocromático',
                    'protocolo' => 'WiFi'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sensor de Qualidade do Ar',
                'description' => 'Sensor multi-parâmetro para monitoramento da qualidade do ar',
                'icon' => '🌬️',
                'specifications' => [
                    'voltagem' => '5V',
                    'parametros' => 'CO2, VOCs, PM2.5, PM10',
                    'range_co2' => '400-10000 ppm',
                    'precisao_co2' => '±50ppm',
                    'protocolo' => 'WiFi',
                    'tempo_resposta' => '60s'
                ],
                'is_active' => true,
            ],
        ];

        foreach ($deviceTypes as $deviceType) {
            DeviceType::create($deviceType);
        }

        $this->command->info('✅ Device Types seeded successfully with specifications!');
    }
}
