# API MQTT - Laravel

Esta é uma API desenvolvida em Laravel para gerenciar tópicos MQTT e enviar mensagens para dispositivos IoT.

## Requisitos

- PHP >= 8.1
- Composer
- MySQL
- Servidor MQTT (Mosquitto)

## Instalação

1. Clone o repositório:
```bash
git clone <repository-url>
cd api-mqtt
```

2. Instale as dependências:
```bash
composer install
```

3. Configure o arquivo de ambiente:
```bash
cp .env.example .env
```

4. Configure as variáveis de ambiente no arquivo `.env`:
```env
APP_NAME="API MQTT"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mqtt
DB_USERNAME=roboflex
DB_PASSWORD=Roboflex()123

# MQTT Configuration
MQTT_HOST=localhost
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_CLIENT_ID=laravel_mqtt_client
```

5. Gere a chave da aplicação:
```bash
php artisan key:generate
```

6. Execute as migrações:
```bash
php artisan migrate
```

## Executando o servidor

Para desenvolvimento:
```bash
php artisan serve
```

O servidor estará disponível em `http://localhost:8000`

## Endpoints da API

### 1. Criar Tópico
**POST** `/api/mqtt/topics`

**Parâmetros:**
- `name` (string, obrigatório): Nome do tópico
- `description` (string, opcional): Descrição do tópico

**Exemplo:**
```bash
curl -X POST http://localhost:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{
    "name": "dispositivo/porta",
    "description": "Controle da porta principal"
  }'
```

### 2. Enviar Mensagem
**POST** `/api/mqtt/send-message`

**Parâmetros:**
- `topico` (string, obrigatório): Nome do tópico
- `mensagem` (string, obrigatório): Mensagem a ser enviada

**Exemplo:**
```bash
curl -X POST http://localhost:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "topico": "dispositivo/porta",
    "mensagem": "liberar"
  }'
```

### 3. Listar Tópicos
**GET** `/api/mqtt/topics`

**Exemplo:**
```bash
curl -X GET http://localhost:8000/api/mqtt/topics
```

### 4. Mostrar Tópico Específico
**GET** `/api/mqtt/topics/{id}`

**Exemplo:**
```bash
curl -X GET http://localhost:8000/api/mqtt/topics/1
```

### 5. Desativar Tópico
**PATCH** `/api/mqtt/topics/{id}/deactivate`

**Exemplo:**
```bash
curl -X PATCH http://localhost:8000/api/mqtt/topics/1/deactivate
```

## Funcionalidades

### Validação de Tópicos
A API verifica se o tópico existe no banco de dados antes de enviar mensagens. Se o tópico não existir ou estiver inativo, retorna erro 404.

### Controle de Dispositivos
Quando a mensagem "liberar" é enviada para um tópico, os dispositivos IoT conectados devem:
1. Receber a mensagem "liberado"
2. Alterar o nível do GPIO de baixo para alto

### Estrutura do Banco de Dados

**Tabela: topics**
- `id` (primary key)
- `name` (string, unique)
- `description` (text, nullable)
- `is_active` (boolean, default: true)
- `created_at` (timestamp)
- `updated_at` (timestamp)

## Configuração do Servidor MQTT

Certifique-se de que o servidor Mosquitto esteja rodando:

```bash
# Instalar Mosquitto (Ubuntu/Debian)
sudo apt-get install mosquitto mosquitto-clients

# Iniciar o serviço
sudo systemctl start mosquitto
sudo systemctl enable mosquitto
```

## Exemplo de Código para Dispositivo IoT

### Python (Raspberry Pi/Arduino)
```python
import paho.mqtt.client as mqtt
import RPi.GPIO as GPIO

# Configurar GPIO
GPIO.setmode(GPIO.BCM)
GPIO.setup(18, GPIO.OUT)  # Pino 18 como saída
GPIO.output(18, GPIO.LOW)  # Inicialmente baixo

def on_message(client, userdata, msg):
    if msg.topic == "dispositivo/porta":
        if msg.payload.decode() == "liberar":
            GPIO.output(18, GPIO.HIGH)  # Ativar GPIO
            print("Dispositivo liberado!")

client = mqtt.Client()
client.on_message = on_message
client.connect("localhost", 1883, 60)
client.subscribe("dispositivo/porta")
client.loop_forever()
```

### Arduino
```cpp
#include <WiFi.h>
#include <PubSubClient.h>

const char* ssid = "SUA_REDE_WIFI";
const char* password = "SUA_SENHA";
const char* mqtt_server = "SEU_SERVIDOR_MQTT";

WiFiClient espClient;
PubSubClient client(espClient);

const int ledPin = 2;  // Pino do LED/GPIO

void setup() {
  pinMode(ledPin, OUTPUT);
  digitalWrite(ledPin, LOW);
  
  WiFi.begin(ssid, password);
  
  client.setServer(mqtt_server, 1883);
  client.setCallback(callback);
}

void callback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }
  
  if (String(topic) == "dispositivo/porta" && message == "liberar") {
    digitalWrite(ledPin, HIGH);
  }
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();
  
  client.subscribe("dispositivo/porta");
}
```

## Desenvolvimento

Para desenvolvimento com hot reload:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## Produção

Para produção, configure:
1. `APP_ENV=production`
2. `APP_DEBUG=false`
3. Configure o banco de dados de produção
4. Execute `php artisan config:cache`
5. Execute `php artisan route:cache`
6. Execute `php artisan view:cache`
