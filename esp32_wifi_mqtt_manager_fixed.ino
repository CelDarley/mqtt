/*
 * ESP32 WiFi Manager - NOVA ABORDAGEM SIMPLIFICADA
 * - Captive portal apenas para WiFi
 * - Salva MAC no localStorage
 * - Aguarda configuração via app móvel
 * - Endpoint para receber configuração do tópico MQTT
 */

#include <WiFi.h>
#include <WebServer.h>
#include <DNSServer.h>
#include <EEPROM.h>
#include <ArduinoJson.h>
#include <esp_wifi.h>
#include <PubSubClient.h>
#include <ESPmDNS.h>

// ====== CONFIGURACOES ======
#define LED_PIN 48          // LED interno ESP32-S3-WROOM
#define LED_EXTERNAL_PIN 16 // LED externo GPIO16 (status do sistema)
#define LED_MQTT_PIN 19     // LED GPIO19 (indicador de mensagens MQTT)
#define BUTTON_PIN 0        // Botao PROG (GPIO0)
#define EEPROM_SIZE 512     // Tamanho EEPROM
#define AP_SSID "IOT-Zontec"
#define AP_PASSWORD "12345678"

// Estados do LED
#define LED_OFF 0
#define LED_ON 1
#define LED_FAST_BLINK 2
#define LED_SLOW_BLINK 3

// Estrutura para credenciais WiFi
struct WiFiCredentials {
  char ssid[32];
  char password[64];
  bool valid;
};

// Estrutura para configuração MQTT
struct MQTTConfig {
  char broker[64];
  int port;
  char topic[64];
  bool valid;
};

// ====== VARIAVEIS GLOBAIS ======
WebServer server(5000);
DNSServer dnsServer;
WiFiClient wifiClient;
PubSubClient mqttClient(wifiClient);

WiFiCredentials wifiCreds;
MQTTConfig mqttConfig;

// Estado do sistema
bool ap_mode = false;
bool wifi_connected = false;
bool mqtt_connected = false;
int led_state = LED_OFF;
unsigned long last_led_update = 0;
bool led_on = false;

// LED MQTT
unsigned long mqtt_led_timer = 0;
bool mqtt_led_active = false;

// Botão
unsigned long button_press_start = 0;
bool button_pressed = false;
const unsigned long LONG_PRESS_TIME = 10000; // 10 segundos

// ====== HTML SIMPLIFICADO PARA NOVA ABORDAGEM ======
const char* html_page = R"rawliteral(<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ESP32 WiFi Setup</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { 
  font-family: Arial, sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
}
.container { 
  background: white; border-radius: 20px; padding: 40px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1); max-width: 500px; width: 90%;
}
.logo { text-align: center; margin-bottom: 30px; }
.logo h1 { color: #333; font-size: 28px; margin-bottom: 5px; }
.logo p { color: #666; font-size: 14px; }
.form-group { margin-bottom: 25px; }
.form-group label { 
  display: block; margin-bottom: 8px; color: #333; 
  font-weight: 500; font-size: 14px;
}
.form-group input { 
  width: 100%; padding: 15px; border: 2px solid #e1e5e9;
  border-radius: 10px; font-size: 16px; transition: all 0.3s;
}
.password-container { position: relative; }
.password-toggle { 
  position: absolute; right: 15px; top: 15px; 
  cursor: pointer; color: #666; font-size: 12px;
  background: #f8f9fa; padding: 5px 8px; border-radius: 5px;
  border: 1px solid #ddd; user-select: none;
}
.password-toggle:hover { background: #e9ecef; }
.btn { 
  width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white; border: none; border-radius: 10px; font-size: 16px;
  font-weight: 600; cursor: pointer; transition: all 0.3s;
}
.btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
.status { 
  margin-top: 20px; padding: 15px; border-radius: 10px;
  text-align: center; font-weight: 500; display: none;
}
.status.success { background: #d4edda; color: #155724; }
.status.error { background: #f8d7da; color: #721c24; }
.status.loading { background: #d1ecf1; color: #0c5460; }
.spinner { 
  border: 2px solid #f3f3f3; border-top: 2px solid #0c5460;
  border-radius: 50%; width: 16px; height: 16px;
  animation: spin 1s linear infinite; display: inline-block; margin-right: 8px;
}
.device-info {
  background: #f8f9fa; padding: 15px; border-radius: 10px; margin-top: 20px;
  border-left: 4px solid #667eea; display: none;
}
.device-info h3 { color: #333; margin-bottom: 10px; font-size: 16px; }
.mac-address { font-family: monospace; font-weight: bold; color: #764ba2; }
.next-step {
  background: #e3f2fd; padding: 15px; border-radius: 10px; margin-top: 15px;
  border-left: 4px solid #2196f3; display: none;
}
.next-step h4 { color: #1565c0; margin-bottom: 8px; }
.next-step p { color: #1976d2; font-size: 14px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
</head>
<body>
<div class="container">
<div class="logo">
<h1>ESP32 IoT Setup</h1>
<p>Configuração da conexão WiFi</p>
</div>
<form id="wifi-form">
<div class="form-group">
<label>Nome da Rede (SSID)</label>
<input type="text" id="ssid" required placeholder="Nome da sua rede WiFi">
</div>
<div class="form-group">
<label>Senha da Rede</label>
<div class="password-container">
<input type="password" id="password" required placeholder="Senha da rede">
<span class="password-toggle" onclick="togglePassword()">Ver</span>
</div>
</div>
<button type="submit" class="btn">Conectar WiFi</button>
</form>
<div id="status" class="status"></div>
<div id="device-info" class="device-info">
<h3>Dispositivo Conectado!</h3>
<p>MAC Address: <span id="device-mac" class="mac-address"></span></p>
</div>
<div id="next-step" class="next-step">
<h4>Próximo Passo</h4>
<p>Configuração WiFi em andamento...</p>
</div>
</div>
<script>
function togglePassword() {
  var input = document.getElementById('password');
  var toggle = document.querySelector('.password-toggle');
  if (input.type === 'password') {
    input.type = 'text';
    toggle.innerHTML = 'Ocultar';
  } else {
    input.type = 'password';
    toggle.innerHTML = 'Ver';
  }
}

function showStatus(msg, type) {
  var s = document.getElementById('status');
  s.className = 'status ' + type;
  if (type === 'loading') {
    s.innerHTML = '<span class="spinner"></span>' + msg;
  } else {
    s.innerHTML = msg;
  }
  s.style.display = 'block';
}

function showDeviceInfo(macAddress) {
  document.getElementById('device-mac').textContent = macAddress;
  document.getElementById('device-info').style.display = 'block';
  
  // Salvar MAC no localStorage com debug detalhado
  console.log('🔧 Salvando MAC no localStorage...');
  console.log('MAC a ser salvo:', macAddress);
  console.log('Tipo do MAC:', typeof macAddress);
  console.log('localStorage antes:', localStorage.getItem('esp32_mac_address'));
  
  localStorage.setItem('esp32_mac_address', macAddress);
  
  // Verificar se foi salvo corretamente
  const savedMac = localStorage.getItem('esp32_mac_address');
  console.log('✅ MAC Address salvo no localStorage:', savedMac);
  console.log('🔍 Verificação: salvamento', savedMac === macAddress ? 'SUCESSO' : 'FALHOU');
  
  // Mostrar alerta visual para debug
  alert(`🔧 Debug localStorage:\n\nMAC salvo: ${savedMac}\nTipo: ${typeof savedMac}\nComprimento: ${savedMac ? savedMac.length : 'null'}\n\nEste MAC será usado pelo aplicativo de configuração.`);
}

function showCompletionMessage() {
  document.getElementById('next-step').style.display = 'block';
  
  // Atualizar mensagem para ser mais clara
  var nextStep = document.getElementById('next-step');
  nextStep.innerHTML = `
    <h4>✅ Configuração Concluída</h4>
    <p><strong>Dispositivo conectado à rede WiFi com sucesso!</strong></p>
    <p>🚀 <strong>Próximo passo:</strong> Acesse agora o aplicativo de configuração para completar o setup do dispositivo.</p>
    <p>💡 <em>Você pode fechar esta janela e conectar-se à sua rede WiFi principal.</em></p>
  `;
}

document.getElementById('wifi-form').addEventListener('submit', function(e) {
  e.preventDefault();
  var ssid = document.getElementById('ssid').value;
  var pass = document.getElementById('password').value;
  
  if (!ssid || !pass) {
    showStatus('Preencha todos os campos!', 'error');
    return;
  }
  
  showStatus('Conectando ao WiFi...', 'loading');
  
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '/api/connect');
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.timeout = 20000;
  
  xhr.onload = function() {
    if (xhr.status === 200) {
      var result = JSON.parse(xhr.responseText);
              if (result.success) {
          showStatus('WiFi conectado com sucesso!', 'success');
          
          // Mostrar informações do dispositivo
          var macAddress = result.device_info ? result.device_info.mac_address : 'UNKNOWN';
          showDeviceInfo(macAddress);
          
          setTimeout(function() {
            showStatus('Configuração WiFi concluída!', 'success');
            showCompletionMessage();
          }, 2000);
          
        } else {
          showStatus('Erro: ' + result.message, 'error');
        }
    } else {
      showStatus('Falha na conexão - Código: ' + xhr.status, 'error');
    }
  };
  
  xhr.ontimeout = function() {
    showStatus('Timeout - Verifique SSID e senha', 'error');
  };
  
  xhr.onerror = function() {
    showStatus('Erro de rede - Tente novamente', 'error');
  };
  
  xhr.send(JSON.stringify({ssid: ssid, password: pass}));
});
</script>
</body>
</html>)rawliteral";

// ====== FUNCOES DE LED ======
void setLedState(int state) {
  led_state = state;
  last_led_update = millis();
  
  switch(state) {
    case LED_OFF:
      digitalWrite(LED_PIN, LOW);
      digitalWrite(LED_EXTERNAL_PIN, LOW);
      led_on = false;
      break;
    case LED_ON:
      digitalWrite(LED_PIN, HIGH);
      digitalWrite(LED_EXTERNAL_PIN, HIGH);
      led_on = true;
      break;
    case LED_FAST_BLINK:
    case LED_SLOW_BLINK:
      break;
  }
}

void updateLed() {
  unsigned long now = millis();
  unsigned long interval = (led_state == LED_FAST_BLINK) ? 200 : 1000;
  
  if (led_state == LED_FAST_BLINK || led_state == LED_SLOW_BLINK) {
    if (now - last_led_update >= interval) {
      led_on = !led_on;
      digitalWrite(LED_PIN, led_on ? HIGH : LOW);
      digitalWrite(LED_EXTERNAL_PIN, led_on ? HIGH : LOW);
      last_led_update = now;
    }
  }
}

// ====== FUNCOES EEPROM ======
void saveWiFiCredentials(const char* ssid, const char* password) {
  WiFiCredentials creds;
  memset(&creds, 0, sizeof(creds));
  
  strncpy(creds.ssid, ssid, sizeof(creds.ssid) - 1);
  strncpy(creds.password, password, sizeof(creds.password) - 1);
  creds.valid = true;
  
  EEPROM.put(0, creds);
  EEPROM.commit();
  
  Serial.println("WiFi credentials saved to EEPROM");
}

bool loadWiFiCredentials() {
  EEPROM.get(0, wifiCreds);
  
  if (!wifiCreds.valid) {
    Serial.println("No valid WiFi credentials in EEPROM");
    return false;
  }
  
  Serial.printf("Credentials loaded: SSID=%s\n", wifiCreds.ssid);
  return true;
}

void clearEEPROM() {
  for (int i = 0; i < EEPROM_SIZE; i++) {
    EEPROM.write(i, 0);
  }
  EEPROM.commit();
  Serial.println("EEPROM cleared - Factory Reset");
}

// ====== FUNCOES MQTT ======
void saveMQTTConfig(const char* broker, int port, const char* topic) {
  memset(&mqttConfig, 0, sizeof(mqttConfig));
  
  strncpy(mqttConfig.broker, broker, sizeof(mqttConfig.broker) - 1);
  mqttConfig.port = port;
  strncpy(mqttConfig.topic, topic, sizeof(mqttConfig.topic) - 1);
  mqttConfig.valid = true;
  
  EEPROM.put(sizeof(WiFiCredentials), mqttConfig);
  EEPROM.commit();
  
  Serial.printf("MQTT config saved: %s:%d, topic: %s\n", broker, port, topic);
}

bool loadMQTTConfig() {
  EEPROM.get(sizeof(WiFiCredentials), mqttConfig);
  
  if (!mqttConfig.valid) {
    Serial.println("No valid MQTT config in EEPROM");
    return false;
  }
  
  Serial.printf("MQTT config loaded: %s:%d, topic: %s\n", 
                mqttConfig.broker, mqttConfig.port, mqttConfig.topic);
  return true;
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
  payload[length] = '\0';
  String message = String((char*)payload);
  
  Serial.printf("MQTT message received [%s]: %s\n", topic, message.c_str());
  
  // ACENDER LED GPIO19 quando recebe mensagem
  digitalWrite(LED_MQTT_PIN, HIGH);
  mqtt_led_active = true;
  mqtt_led_timer = millis();
  
  // Processar comandos JSON
  DynamicJsonDocument doc(256);
  if (deserializeJson(doc, message) == DeserializationError::Ok) {
    String command = doc["command"];
    
    if (command == "led_on") {
      setLedState(LED_ON);
      Serial.println("LED ligado via MQTT");
    }
    else if (command == "led_off") {
      setLedState(LED_OFF);
      Serial.println("LED desligado via MQTT");
    }
    else if (command == "led_blink") {
      setLedState(LED_FAST_BLINK);
      Serial.println("LED piscando via MQTT");
    }
    else if (command == "get_status") {
      publishStatus();
    }
  } else {
    // Comando direto
    if (message == "led_on") {
      setLedState(LED_ON);
    } else if (message == "led_off") {
      setLedState(LED_OFF);
    } else if (message == "led_blink") {
      setLedState(LED_FAST_BLINK);
    }
  }
}

void publishStatus() {
  if (!mqttClient.connected()) return;
  
  DynamicJsonDocument doc(512);
  doc["device_mac"] = WiFi.macAddress();
  doc["wifi_connected"] = wifi_connected;
  doc["wifi_ip"] = WiFi.localIP().toString();
  doc["wifi_ssid"] = WiFi.SSID();
  doc["mqtt_connected"] = mqtt_connected;
  doc["uptime"] = millis();
  doc["free_heap"] = ESP.getFreeHeap();
  doc["led_state"] = led_state;
  doc["firmware_version"] = "ESP32-MQTT-v3.0";
  
  String response;
  serializeJson(doc, response);
  
  String responseTopic = String(mqttConfig.topic) + "/status";
  mqttClient.publish(responseTopic.c_str(), response.c_str());
  
  Serial.printf("Status publicado via MQTT: %s\n", responseTopic.c_str());
}

bool connectMQTT() {
  if (!wifi_connected || !loadMQTTConfig()) {
    return false;
  }
  
  if (!mqttClient.connected()) {
    mqttClient.setServer(mqttConfig.broker, mqttConfig.port);
    mqttClient.setCallback(mqttCallback);
    
    String clientId = "ESP32-" + WiFi.macAddress();
    clientId.replace(":", "");
    
    Serial.printf("Connecting to MQTT: %s:%d\n", mqttConfig.broker, mqttConfig.port);
    
    if (mqttClient.connect(clientId.c_str())) {
      mqtt_connected = true;
      Serial.println("MQTT connected!");
      
      mqttClient.subscribe(mqttConfig.topic);
      Serial.printf("Subscribed to: %s\n", mqttConfig.topic);
      return true;
    } else {
      mqtt_connected = false;
      Serial.printf("MQTT failed: %d\n", mqttClient.state());
      return false;
    }
  }
  
  return mqtt_connected;
}

void updateMQTTLed() {
  if (mqtt_led_active && millis() - mqtt_led_timer >= 2000) {
    digitalWrite(LED_MQTT_PIN, LOW);
    mqtt_led_active = false;
  }
}

// ====== FUNCOES WiFi ======
void startAccessPoint() {
  Serial.println("Starting Access Point...");
  
  WiFi.mode(WIFI_AP);
  WiFi.softAPConfig(IPAddress(192,168,4,1), IPAddress(192,168,4,1), IPAddress(255,255,255,0));
  
  if (!WiFi.softAP(AP_SSID, AP_PASSWORD)) {
    Serial.println("Failed to start AP!");
    return;
  }
  
  ap_mode = true;
  wifi_connected = false;
  setLedState(LED_FAST_BLINK);
  
  dnsServer.start(53, "*", IPAddress(192,168,4,1));
  
  Serial.printf("AP active: %s\n", AP_SSID);
  Serial.println("Access: http://192.168.4.1:5000");
}

bool connectToWiFi() {
  if (!loadWiFiCredentials()) {
    return false;
  }
  
  Serial.printf("Connecting to WiFi: %s\n", wifiCreds.ssid);
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(wifiCreds.ssid, wifiCreds.password);
  
  setLedState(LED_SLOW_BLINK);
  
  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 15000) {
    delay(500);
    Serial.printf("[%d] ", WiFi.status());
    updateLed();
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    wifi_connected = true;
    ap_mode = false;
    setLedState(LED_ON);
    
    Serial.println();
    Serial.printf("WiFi connected! IP: %s\n", WiFi.localIP().toString().c_str());
    
    // Inicializar mDNS
    String hostname = "esp32-iot-" + WiFi.macAddress();
    hostname.replace(":", "");
    hostname.toLowerCase();
    
    if (MDNS.begin(hostname.c_str())) {
      MDNS.addService("http", "tcp", 5000);
      Serial.printf("mDNS responder started: %s.local\n", hostname.c_str());
    }
    
    return true;
  } else {
    Serial.println("\nFailed to connect WiFi");
    wifi_connected = false;
    return false;
  }
}

// ====== INTERFACE WEB ======
void handleRoot() {
  server.send(200, "text/html", html_page);
}

void handleConnect() {
  if (server.method() != HTTP_POST) {
    server.send(405, "application/json", "{\"success\":false,\"message\":\"Method not allowed\"}");
    return;
  }
  
  String body = server.arg("plain");
  DynamicJsonDocument doc(256);
  
  if (deserializeJson(doc, body) != DeserializationError::Ok) {
    server.send(400, "application/json", "{\"success\":false,\"message\":\"Invalid JSON\"}");
    return;
  }
  
  String ssid = doc["ssid"];
  String password = doc["password"];
  
  if (ssid.length() == 0 || password.length() == 0) {
    server.send(400, "application/json", "{\"success\":false,\"message\":\"SSID and password required\"}");
    return;
  }
  
  // Obter MAC address antes de mudar modo WiFi
  String macAddress = WiFi.macAddress();
  if (macAddress == "00:00:00:00:00:00" || macAddress.length() < 12) {
    uint8_t mac[6];
    esp_wifi_get_mac(WIFI_IF_AP, mac);
    macAddress = String(mac[0], HEX) + ":" + String(mac[1], HEX) + ":" + 
                 String(mac[2], HEX) + ":" + String(mac[3], HEX) + ":" + 
                 String(mac[4], HEX) + ":" + String(mac[5], HEX);
    macAddress.toUpperCase();
  }
  
  Serial.printf("Device MAC: %s\n", macAddress.c_str());
  
  saveWiFiCredentials(ssid.c_str(), password.c_str());
  
  String response = "{\"success\":true,\"message\":\"Connecting...\",\"device_info\":{\"mac_address\":\"" + macAddress + "\"}}";
  server.send(200, "application/json", response);
  
  delay(1000);
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid.c_str(), password.c_str());
  
  setLedState(LED_SLOW_BLINK);
  
  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 15000) {
    delay(500);
    updateLed();
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    wifi_connected = true;
    ap_mode = false;
    setLedState(LED_ON);
    Serial.println("\nWiFi connection successful!");
  } else {
    Serial.println("\nWiFi connection failed!");
    startAccessPoint();
  }
}

// NOVO: Endpoint para configurar MQTT via app
void handleMQTTConfig() {
  if (server.method() != HTTP_POST) {
    server.send(405, "application/json", "{\"success\":false,\"message\":\"Method not allowed\"}");
    return;
  }
  
  String body = server.arg("plain");
  DynamicJsonDocument doc(512);
  
  if (deserializeJson(doc, body) != DeserializationError::Ok) {
    server.send(400, "application/json", "{\"success\":false,\"message\":\"Invalid JSON\"}");
    return;
  }
  
  String broker = doc["broker"];
  String topic = doc["topic"];
  int port = doc["port"] | 1883;
  
  if (broker.length() == 0 || topic.length() == 0) {
    server.send(400, "application/json", "{\"success\":false,\"message\":\"Broker and topic required\"}");
    return;
  }
  
  // Salvar configuração MQTT
  saveMQTTConfig(broker.c_str(), port, topic.c_str());
  
  // Conectar MQTT
  bool connected = connectMQTT();
  
  String response = "{\"success\":" + String(connected ? "true" : "false") + 
                   ",\"message\":\"" + (connected ? "MQTT configured and connected" : "MQTT configured but connection failed") + "\"}";
  
  server.send(200, "application/json", response);
  
  Serial.printf("MQTT configured: %s:%d/%s - Connected: %s\n", 
                broker.c_str(), port, topic.c_str(), connected ? "Yes" : "No");
}

void handleStatus() {
  DynamicJsonDocument doc(512);
  doc["device_mac"] = WiFi.macAddress();
  doc["ap_mode"] = ap_mode;
  doc["wifi_connected"] = wifi_connected;
  doc["mqtt_connected"] = mqtt_connected;
  doc["free_heap"] = ESP.getFreeHeap();
  doc["uptime"] = millis();
  doc["led_state"] = led_state;
  
  if (wifi_connected) {
    doc["wifi_ip"] = WiFi.localIP().toString();
    doc["wifi_ssid"] = WiFi.SSID();
  }
  
  if (loadMQTTConfig()) {
    doc["mqtt_broker"] = mqttConfig.broker;
    doc["mqtt_port"] = mqttConfig.port;
    doc["mqtt_topic"] = mqttConfig.topic;
  }
  
  String response;
  serializeJson(doc, response);
  server.send(200, "application/json", response);
}

void handleNotFound() {
  if (ap_mode) {
    String redirectHTML = "<html><head><script>window.location.href='http://192.168.4.1:5000';</script></head></html>";
    server.send(200, "text/html", redirectHTML);
  } else {
    server.send(404, "text/plain", "Not Found");
  }
}

// ====== FUNCOES DE BOTAO ======
void handleButton() {
  static unsigned long last_read = 0;
  if (millis() - last_read < 50) return;
  last_read = millis();
  
  bool current_state = digitalRead(BUTTON_PIN) == LOW;
  
  if (current_state && !button_pressed) {
    button_pressed = true;
    button_press_start = millis();
  }
  else if (!current_state && button_pressed) {
    button_pressed = false;
    unsigned long press_duration = millis() - button_press_start;
    
    if (press_duration >= LONG_PRESS_TIME) {
      Serial.println("Factory Reset - Button pressed for 10+ seconds");
      setLedState(LED_FAST_BLINK);
      clearEEPROM();
      delay(2000);
      ESP.restart();
    }
    else if (press_duration >= 1000) {
      Serial.println("Toggling WiFi mode");
      if (ap_mode) {
        connectToWiFi();
      } else {
        startAccessPoint();
      }
    }
  }
}

// ====== SETUP ======
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\nESP32 WiFi Manager - Nova Abordagem Simplificada");
  
  // Configurar pinos
  pinMode(LED_PIN, OUTPUT);
  pinMode(LED_EXTERNAL_PIN, OUTPUT);
  pinMode(LED_MQTT_PIN, OUTPUT);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  
  digitalWrite(LED_MQTT_PIN, LOW);
  
  // Teste LED GPIO19
  Serial.println("🧪 Testando LED GPIO19...");
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_MQTT_PIN, HIGH);
    delay(300);
    digitalWrite(LED_MQTT_PIN, LOW);
    delay(300);
  }
  
  if (!EEPROM.begin(EEPROM_SIZE)) {
    Serial.println("Failed to initialize EEPROM");
    return;
  }
  
  setLedState(LED_SLOW_BLINK);
  
  // Tentar conectar ao WiFi salvo
  bool connected = connectToWiFi();
  
  if (!connected) {
    startAccessPoint();
  }
  
  // Configurar rotas do servidor
  server.on("/", handleRoot);
  server.on("/config", handleRoot);
  server.on("/api/connect", HTTP_POST, handleConnect);
  server.on("/api/mqtt/config", HTTP_POST, handleMQTTConfig);  // NOVO
  server.on("/api/status", handleStatus);
  server.onNotFound(handleNotFound);
  
  server.enableCORS(true);
  server.begin();
  
  Serial.println("Web server started on port 5000");
  
  // Conectar MQTT se configurado
  if (wifi_connected && loadMQTTConfig()) {
    connectMQTT();
  }
  
  Serial.println("Setup complete - Aguardando configuração via app!");
}

// ====== LOOP PRINCIPAL ======
void loop() {
  updateLed();
  updateMQTTLed();
  handleButton();
  server.handleClient();
  
  if (ap_mode) {
    dnsServer.processNextRequest();
  }
  
  // Verificar conexão WiFi
  if (wifi_connected && WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected");
    wifi_connected = false;
    mqtt_connected = false;
    setLedState(LED_SLOW_BLINK);
    connectToWiFi();
  }
  
  // Gerenciar MQTT
  if (wifi_connected) {
    if (!mqttClient.connected()) {
      mqtt_connected = false;
      if (loadMQTTConfig()) {
        connectMQTT();
      }
    } else {
      mqttClient.loop();
    }
  }
  
  delay(10);
} 