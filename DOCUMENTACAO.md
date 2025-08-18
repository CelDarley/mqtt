# API MQTT - Documentação Completa

## Visão Geral

Esta é uma API desenvolvida em Laravel para gerenciar tópicos MQTT e enviar mensagens para dispositivos IoT. A API permite:

- Criar e gerenciar tópicos MQTT
- Enviar mensagens para tópicos específicos
- Validar a existência de tópicos antes do envio
- Controlar dispositivos IoT através de mensagens MQTT

## Arquitetura

### Tecnologias Utilizadas

- **Backend**: Laravel 12.x
- **Banco de Dados**: MySQL
- **Broker MQTT**: Mosquitto
- **Cliente MQTT**: php-mqtt/client

### Estrutura do Projeto

```
api-mqtt/
├── app/
│   ├── Http/Controllers/
│   │   └── TopicController.php
│   └── Models/
│       └── Topic.php
├── config/
│   └── mqtt.php
├── database/migrations/
│   └── create_topics_table.php
├── routes/
│   └── api.php
├── exemplo_dispositivo.py
├── teste_api.py
└── README.md
```

## Configuração

### Requisitos

- PHP >= 8.1
- Composer
- MySQL
- Mosquitto MQTT Broker

### Instalação

1. **Clone o repositório**
```bash
git clone <repository-url>
cd api-mqtt
```

2. **Instale as dependências**
```bash
composer install
```

3. **Configure o ambiente**
```bash
cp .env.example .env
```

4. **Configure o arquivo .env**
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

5. **Gere a chave da aplicação**
```bash
php artisan key:generate
```

6. **Configure o banco de dados**
```bash
# Criar usuário e banco (se necessário)
sudo mysql -e "CREATE USER IF NOT EXISTS 'roboflex'@'localhost' IDENTIFIED BY 'Roboflex()123'; GRANT ALL PRIVILEGES ON mqtt.* TO 'roboflex'@'localhost'; CREATE DATABASE IF NOT EXISTS mqtt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; FLUSH PRIVILEGES;"
```

7. **Execute as migrações**
```bash
php artisan migrate
```

8. **Instale e configure o Mosquitto**
```bash
sudo apt-get install mosquitto mosquitto-clients
sudo systemctl start mosquitto
sudo systemctl enable mosquitto
```

9. **Inicie o servidor**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

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

**Resposta:**
```json
{
  "success": true,
  "message": "Tópico criado com sucesso",
  "data": {
    "id": 1,
    "name": "dispositivo/porta",
    "description": "Controle da porta principal",
    "is_active": true,
    "created_at": "2025-08-05T23:16:13.000000Z",
    "updated_at": "2025-08-05T23:16:13.000000Z"
  }
}
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

**Resposta:**
```json
{
  "success": true,
  "message": "Mensagem enviada com sucesso para o tópico: dispositivo/porta",
  "data": {
    "topic": "dispositivo/porta",
    "message": "liberar"
  }
}
```

### 3. Listar Tópicos
**GET** `/api/mqtt/topics`

**Exemplo:**
```bash
curl -X GET http://localhost:8000/api/mqtt/topics
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "dispositivo/porta",
      "description": "Controle da porta principal",
      "is_active": true,
      "created_at": "2025-08-05T23:16:13.000000Z",
      "updated_at": "2025-08-05T23:16:13.000000Z"
    }
  ]
}
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

## Testes

### Teste Automatizado
Execute o script de teste Python:

```bash
python3 teste_api.py
```

### Teste Manual
1. **Criar tópico:**
```bash
curl -X POST http://localhost:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "teste/led", "description": "Teste LED"}'
```

2. **Enviar mensagem:**
```bash
curl -X POST http://localhost:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "teste/led", "mensagem": "liberar"}'
```

3. **Listar tópicos:**
```bash
curl -X GET http://localhost:8000/api/mqtt/topics
```

## Exemplo de Dispositivo IoT

### Python (Raspberry Pi/Arduino)
Execute o exemplo de dispositivo:

```bash
python3 exemplo_dispositivo.py
```

Este script simula um dispositivo IoT que:
- Conecta ao broker MQTT
- Inscreve no tópico "dispositivo/led"
- Processa mensagens "liberar" e "bloquear"
- Simula controle de GPIO

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

## Fluxo de Funcionamento

1. **Criação de Tópico**: Administrador cria um tópico via API
2. **Validação**: API verifica se o tópico existe antes de enviar mensagens
3. **Envio de Mensagem**: API envia mensagem para o broker MQTT
4. **Recebimento**: Dispositivo IoT recebe a mensagem
5. **Ação**: Dispositivo executa a ação (ativar GPIO, etc.)

## Segurança

- Validação de entrada em todos os endpoints
- Verificação de existência de tópicos
- Tratamento de erros robusto
- Logs de operações

## Monitoramento

### Logs do Laravel
```bash
tail -f storage/logs/laravel.log
```

### Status do Mosquitto
```bash
sudo systemctl status mosquitto
```

### Conexões MQTT
```bash
mosquitto_sub -h localhost -t "#" -v
```

## Troubleshooting

### Problema: Erro de conexão MQTT
**Solução**: Verifique se o Mosquitto está rodando
```bash
sudo systemctl status mosquitto
sudo systemctl start mosquitto
```

### Problema: Erro de banco de dados
**Solução**: Verifique as configurações do .env e execute as migrações
```bash
php artisan migrate:fresh
```

### Problema: Tópico não encontrado
**Solução**: Verifique se o tópico foi criado corretamente
```bash
curl -X GET http://localhost:8000/api/mqtt/topics
```

## Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT.

## Suporte

Para suporte, entre em contato através dos issues do GitHub. 