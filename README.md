# API MQTT - Laravel

Esta é uma API desenvolvida em Laravel para gerenciar tópicos MQTT e enviar mensagens para dispositivos IoT.

## 🚀 Requisitos do Sistema

### **Ambiente Recomendado (Produção):**
- **PHP >= 8.2** (recomendado: PHP 8.3)
- **Laravel >= 12.0**
- **Composer >= 2.0**
- **MySQL 8.0** ou **SQLite 3**
- **Servidor MQTT (Mosquitto)**
- **Ubuntu 22.04 LTS** ou superior

### **Ambiente Mínimo (Compatibilidade):**
- **PHP >= 7.4** (Laravel 8.x)
- **Laravel 8.x**
- **Composer >= 1.10**
- **MySQL 5.7** ou **SQLite 3**

## 📋 Extensões PHP Necessárias

```bash
# Extensões obrigatórias
php8.2-cli php8.2-common php8.2-mysql php8.2-zip 
php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml 
php8.2-bcmath php8.2-json php8.2-dom php8.2-xmlreader 
php8.2-xmlwriter php8.2-tokenizer php8.2-opcache 
php8.2-fileinfo php8.2-ctype php8.2-phar php8.2-sqlite3
```

## 🛠️ Instalação

### 1. Clone o repositório:
```bash
git clone https://github.com/CelDarley/mqtt.git
cd api-mqtt
```

### 2. Instale as dependências:
```bash
composer install
```

### 3. Configure o arquivo de ambiente:
```bash
cp .env.example .env
```

### 4. Configure as variáveis de ambiente no arquivo `.env`:

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

### 5. Gere a chave da aplicação:
```bash
php artisan key:generate
```

### 6. Execute as migrações:
```bash
php artisan migrate
```

## 🚀 Executando o servidor

### Para desenvolvimento:
```bash
# Laravel 12+ (PHP 8.2+)
php artisan serve

# Laravel 8.x (PHP 7.4+)
php -S localhost:8000 -t public/
```

O servidor estará disponível em `http://localhost:8000`

## 📡 Endpoints da API

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

## 🔧 Funcionalidades

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

## ⚙️ Configuração do Servidor MQTT

### Instalar Mosquitto (Ubuntu/Debian):
```bash
sudo apt-get update
sudo apt-get install mosquitto mosquitto-clients
```

### Iniciar o serviço:
```bash
sudo systemctl start mosquitto
sudo systemctl enable mosquitto
```

### Verificar status:
```bash
sudo systemctl status mosquitto
```

## 🤖 Exemplo de Código para Dispositivo IoT

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

## 🔄 Downgrade para PHP 7.4

Se você precisar usar PHP 7.4, o projeto inclui arquivos de compatibilidade:

### 1. Use o composer.json para Laravel 8:
```bash
cp composer_laravel8_simple.json composer.json
composer install
```

### 2. Use os arquivos de compatibilidade:
```bash
cp bootstrap_app_laravel8.php bootstrap/app.php
cp artisan_laravel8.php artisan
cp public_index_laravel8.php public/index.php
```

### 3. Copie os middlewares:
```bash
cp TrustProxies.php app/Http/Middleware/
cp CheckForMaintenanceMode.php app/Http/Middleware/
cp TrimStrings.php app/Http/Middleware/
cp ConsoleKernel.php app/Console/Kernel.php
cp Handler.php app/Exceptions/Handler.php
cp HttpKernel.php app/Http/Kernel.php
```

## 🚀 Desenvolvimento

Para desenvolvimento com hot reload:

```bash
# Laravel 12+
php artisan serve --host=0.0.0.0 --port=8000

# Laravel 8.x
php -S 0.0.0.0:8000 -t public/
```

## 🏭 Produção

Servidor da API MQTT - 10.100.0.200

/root/

Para produção, configure:

1. `APP_ENV=production`
2. `APP_DEBUG=false`
3. Configure o banco de dados de produção
4. Execute `php artisan config:cache`
5. Execute `php artisan route:cache`
6. Execute `php artisan view:cache`

## 📊 Status do Projeto

- ✅ **Laravel 12.x** - Versão atual
- ✅ **PHP 8.3** - Suporte completo
- ✅ **MQTT Client** - Funcional
- ✅ **API REST** - Implementada
- ✅ **Validação** - Implementada
- ✅ **Documentação** - Completa

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 📞 Suporte

Para suporte, envie um email para [seu-email@exemplo.com] ou abra uma issue no GitHub.

---

**Desenvolvido com ❤️ por CelDarley**
