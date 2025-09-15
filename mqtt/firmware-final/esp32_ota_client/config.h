/*
 * Configurações ESP32 OTA Client
 * ==============================
 * 
 * Arquivo de configuração para personalizar o comportamento
 * do cliente ESP32 OTA conforme o tipo de dispositivo
 */

#ifndef CONFIG_H
#define CONFIG_H

// ========================================
// CONFIGURAÇÕES DO DISPOSITIVO
// ========================================

// Tipo de dispositivo (alterar conforme necessário)
// Opções: sensor_de_temperatura, sensor_de_umidade, led_de_controle, 
//         sensor_de_movimento, rele_de_controle, sensor_de_pressao,
//         camera_de_monitoramento, sensor_de_vibracao, display_oled,
//         sensor_de_qualidade_do_ar
#define DEVICE_TYPE_CONFIG "sensor_de_temperatura"

// Departamento onde o dispositivo está instalado
// Opções: producao, qualidade, manutencao, administrativo
#define DEPARTMENT_CONFIG "producao"

// Versão do firmware (alterar a cada atualização)
#define FIRMWARE_VERSION_CONFIG "1.0.0"

// ========================================
// CONFIGURAÇÕES DE REDE
// ========================================

// WiFi (configurar para cada instalação)
#define WIFI_SSID_CONFIG "SUA_REDE_WIFI"
#define WIFI_PASSWORD_CONFIG "SUA_SENHA_WIFI"

// MQTT Broker
#define MQTT_SERVER_CONFIG "10.102.0.101"
#define MQTT_PORT_CONFIG 1883
#define MQTT_USER_CONFIG ""
#define MQTT_PASSWORD_CONFIG ""

// Servidor OTA
#define OTA_SERVER_CONFIG "http://firmware.iot.local"

// ========================================
// CONFIGURAÇÕES DE HARDWARE
// ========================================

// Pinos dos LEDs (ajustar conforme montagem)
#define PIN_LED_STATUS 2     // LED interno azul
#define PIN_LED_WIFI 16      // LED verde - Status WiFi
#define PIN_LED_MQTT 17      // LED amarelo - Status MQTT
#define PIN_LED_OTA 18       // LED vermelho - Processo OTA

// Pinos dos sensores/atuadores (exemplos)
#define PIN_SENSOR_TEMP 4    // Sensor de temperatura
#define PIN_SENSOR_HUM 5     // Sensor de umidade
#define PIN_LED_CONTROL 19   // LED controlável
#define PIN_RELAY 21         // Relé
#define PIN_BUTTON 0         // Botão (normalmente GPIO0)

// Configurações I2C (para displays, sensores I2C)
#define I2C_SDA 21
#define I2C_SCL 22

// ========================================
// CONFIGURAÇÕES DE TEMPORIZAÇÃO
// ========================================

// Intervalos em milissegundos
#define HEARTBEAT_INTERVAL 30000      // Heartbeat a cada 30 segundos
#define WIFI_CHECK_INTERVAL 10000     // Verificar WiFi a cada 10 segundos
#define MQTT_CHECK_INTERVAL 5000      // Verificar MQTT a cada 5 segundos
#define SENSOR_READ_INTERVAL 5000     // Ler sensores a cada 5 segundos

// Timeouts
#define OTA_TIMEOUT 30000             // Timeout OTA: 30 segundos
#define WIFI_CONNECT_TIMEOUT 30       // Timeout WiFi: 30 tentativas
#define MQTT_CONNECT_ATTEMPTS 5       // Tentativas MQTT: 5

// ========================================
// CONFIGURAÇÕES OTA
// ========================================

#define OTA_RETRY_COUNT 3             // Tentativas de retry OTA
#define OTA_BUFFER_SIZE 1024          // Buffer para download
#define MQTT_BUFFER_SIZE 2048         // Buffer MQTT (para mensagens OTA)

// ========================================
// CONFIGURAÇÕES DE DEBUG
// ========================================

#define DEBUG_ENABLED true            // Habilitar debug serial
#define DEBUG_BAUD_RATE 115200        // Velocidade serial

// Níveis de debug
#define DEBUG_WIFI true               // Debug WiFi
#define DEBUG_MQTT true               // Debug MQTT
#define DEBUG_OTA true                // Debug OTA
#define DEBUG_SENSORS true            // Debug sensores

// ========================================
// CONFIGURAÇÕES ESPECÍFICAS POR TIPO
// ========================================

#if DEVICE_TYPE_CONFIG == "sensor_de_temperatura"
    #define ENABLE_TEMPERATURE_SENSOR true
    #define TEMPERATURE_PIN PIN_SENSOR_TEMP
    #define TEMPERATURE_CALIBRATION 0.0  // Offset de calibração
    
#elif DEVICE_TYPE_CONFIG == "led_de_controle"
    #define ENABLE_LED_CONTROL true
    #define LED_CONTROL_PIN PIN_LED_CONTROL
    #define LED_PWM_CHANNEL 0
    #define LED_PWM_FREQUENCY 5000
    
#elif DEVICE_TYPE_CONFIG == "rele_de_controle"
    #define ENABLE_RELAY_CONTROL true
    #define RELAY_PIN PIN_RELAY
    #define RELAY_ACTIVE_HIGH true
    
#elif DEVICE_TYPE_CONFIG == "sensor_de_movimento"
    #define ENABLE_MOTION_SENSOR true
    #define MOTION_PIN 4
    #define MOTION_TRIGGER_DURATION 5000  // 5 segundos
    
#elif DEVICE_TYPE_CONFIG == "display_oled"
    #define ENABLE_OLED_DISPLAY true
    #define OLED_WIDTH 128
    #define OLED_HEIGHT 64
    #define OLED_ADDRESS 0x3C
#endif

// ========================================
// VALIDAÇÕES
// ========================================

#if !defined(DEVICE_TYPE_CONFIG)
    #error "DEVICE_TYPE_CONFIG deve ser definido!"
#endif

#if !defined(DEPARTMENT_CONFIG)
    #error "DEPARTMENT_CONFIG deve ser definido!"
#endif

#if !defined(FIRMWARE_VERSION_CONFIG)
    #error "FIRMWARE_VERSION_CONFIG deve ser definido!"
#endif

#endif // CONFIG_H 