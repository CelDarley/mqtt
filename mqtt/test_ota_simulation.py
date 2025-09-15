#!/usr/bin/env python3
"""
Simulador de Dispositivos ESP32 para Teste OTA
===============================================

Este script simula dispositivos ESP32 conectados via MQTT
para testar o sistema de atualizações OTA sem hardware real.

Funcionalidades:
- Simula múltiplos dispositivos ESP32
- Conecta via MQTT e simula heartbeats
- Responde a comandos OTA
- Simula download e instalação de firmware
- Envia feedback realista

Uso: python3 test_ota_simulation.py
"""

import json
import time
import random
import hashlib
import threading
import requests
from datetime import datetime
import paho.mqtt.client as mqtt

# ========================================
# CONFIGURAÇÕES
# ========================================

# Configurações MQTT
MQTT_BROKER = "10.102.0.101"
MQTT_PORT = 1883
MQTT_USERNAME = ""
MQTT_PASSWORD = ""

# Configurações dos dispositivos simulados
DEVICE_CONFIGS = [
    {
        "device_id": "A1B2C3D4E5F6",
        "device_type": "sensor_de_temperatura",
        "department": "producao",
        "firmware_version": "1.0.0"
    },
    {
        "device_id": "B2C3D4E5F6A1",
        "device_type": "led_de_controle",
        "department": "producao",
        "firmware_version": "1.0.0"
    },
    {
        "device_id": "C3D4E5F6A1B2",
        "device_type": "sensor_de_movimento",
        "department": "manutencao",
        "firmware_version": "1.0.0"
    },
    {
        "device_id": "D4E5F6A1B2C3",
        "device_type": "rele_de_controle",
        "department": "qualidade",
        "firmware_version": "1.0.0"
    },
    {
        "device_id": "E5F6A1B2C3D4",
        "device_type": "sensor_de_temperatura",
        "department": "producao",
        "firmware_version": "1.0.0"
    }
]

# ========================================
# CLASSE DO DISPOSITIVO SIMULADO
# ========================================

class SimulatedESP32Device:
    def __init__(self, config):
        self.device_id = config["device_id"]
        self.device_type = config["device_type"]
        self.department = config["department"]
        self.firmware_version = config["firmware_version"]
        
        # Estado do dispositivo
        self.connected = False
        self.uptime = 0
        self.free_heap = random.randint(40000, 50000)
        self.wifi_rssi = random.randint(-80, -40)
        
        # Estado OTA
        self.ota_in_progress = False
        self.current_ota_id = None
        
        # Cliente MQTT
        self.mqtt_client = mqtt.Client(f"ESP32_SIM_{self.device_id}")
        self.mqtt_client.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)
        self.mqtt_client.on_connect = self.on_connect
        self.mqtt_client.on_message = self.on_message
        self.mqtt_client.on_disconnect = self.on_disconnect
        
        # Tópicos MQTT
        self.topic_base = f"iot/{self.department}/{self.device_type}/{self.device_id}"
        self.topic_ota = f"{self.topic_base}/ota"
        self.topic_status = f"{self.topic_base}/status"
        self.topic_feedback = f"{self.topic_base}/feedback"
        
        # Thread para heartbeat
        self.heartbeat_thread = None
        self.running = False

    def start(self):
        """Iniciar simulação do dispositivo"""
        print(f"🚀 Iniciando dispositivo simulado: {self.device_id}")
        print(f"   Tipo: {self.device_type}")
        print(f"   Departamento: {self.department}")
        print(f"   Firmware: v{self.firmware_version}")
        print(f"   Tópico base: {self.topic_base}")
        
        # Conectar MQTT
        try:
            self.mqtt_client.connect(MQTT_BROKER, MQTT_PORT, 60)
            self.mqtt_client.loop_start()
            self.running = True
            
            # Iniciar thread de heartbeat
            self.heartbeat_thread = threading.Thread(target=self.heartbeat_loop)
            self.heartbeat_thread.daemon = True
            self.heartbeat_thread.start()
            
        except Exception as e:
            print(f"❌ Erro ao conectar dispositivo {self.device_id}: {e}")

    def stop(self):
        """Parar simulação do dispositivo"""
        print(f"⏹️ Parando dispositivo: {self.device_id}")
        self.running = False
        if self.mqtt_client.is_connected():
            self.send_device_status("offline")
            self.mqtt_client.disconnect()

    def on_connect(self, client, userdata, flags, rc):
        """Callback de conexão MQTT"""
        if rc == 0:
            self.connected = True
            print(f"✅ {self.device_id}: MQTT conectado")
            
            # Subscrever tópico OTA
            client.subscribe(self.topic_ota)
            print(f"📩 {self.device_id}: Subscrito em {self.topic_ota}")
            
            # Enviar status online
            self.send_device_status("online")
            
        else:
            print(f"❌ {self.device_id}: Falha na conexão MQTT: {rc}")

    def on_disconnect(self, client, userdata, rc):
        """Callback de desconexão MQTT"""
        self.connected = False
        print(f"⚠️ {self.device_id}: MQTT desconectado")

    def on_message(self, client, userdata, msg):
        """Callback de mensagem MQTT recebida"""
        try:
            topic = msg.topic
            payload = msg.payload.decode()
            
            print(f"📨 {self.device_id}: Mensagem recebida em {topic}")
            print(f"   Payload: {payload[:100]}...")
            
            # Verificar se é comando OTA
            if topic == self.topic_ota:
                self.process_ota_command(payload)
                
        except Exception as e:
            print(f"❌ {self.device_id}: Erro ao processar mensagem: {e}")

    def process_ota_command(self, payload):
        """Processar comando OTA recebido"""
        try:
            command = json.loads(payload)
            
            if command.get("command") != "ota_update":
                print(f"⚠️ {self.device_id}: Comando não reconhecido")
                return
            
            # Extrair informações do comando
            ota_id = command.get("ota_id")
            target_version = command.get("firmware_version")
            firmware_url = command.get("firmware_url")
            checksum_md5 = command.get("checksum_md5", "")
            force_update = command.get("force_update", False)
            
            print(f"🔄 {self.device_id}: Processando comando OTA")
            print(f"   OTA ID: {ota_id}")
            print(f"   Versão alvo: {target_version}")
            print(f"   URL: {firmware_url}")
            
            # Verificar se já temos essa versão
            if not force_update and target_version == self.firmware_version:
                print(f"ℹ️ {self.device_id}: Já possui a versão {target_version}")
                self.send_ota_feedback(ota_id, "success", f"Versão já instalada: {target_version}")
                return
            
            # Iniciar processo OTA em thread separada
            self.current_ota_id = ota_id
            self.ota_in_progress = True
            
            ota_thread = threading.Thread(
                target=self.simulate_ota_process,
                args=(ota_id, target_version, firmware_url, checksum_md5)
            )
            ota_thread.daemon = True
            ota_thread.start()
            
        except json.JSONDecodeError as e:
            print(f"❌ {self.device_id}: Erro ao decodificar JSON: {e}")
            self.send_ota_feedback("unknown", "failed", "Erro no formato JSON")
        except Exception as e:
            print(f"❌ {self.device_id}: Erro no processamento OTA: {e}")

    def simulate_ota_process(self, ota_id, target_version, firmware_url, expected_checksum):
        """Simular processo completo de OTA"""
        try:
            print(f"🚀 {self.device_id}: Iniciando simulação OTA")
            
            # 1. Enviar feedback inicial
            self.send_ota_feedback(ota_id, "in_progress", "Iniciando download do firmware...")
            time.sleep(1)
            
            # 2. Simular verificação de conectividade
            print(f"🌐 {self.device_id}: Verificando conectividade...")
            time.sleep(1)
            
            # 3. Simular download do firmware
            print(f"⬇️ {self.device_id}: Simulando download...")
            firmware_size = random.randint(800000, 1200000)  # 800KB - 1.2MB
            
            for progress in range(0, 101, 10):
                self.send_ota_progress(ota_id, progress)
                time.sleep(0.5)  # Simular tempo de download
                print(f"📊 {self.device_id}: Download {progress}%")
            
            # 4. Simular verificação MD5 (se fornecido)
            if expected_checksum:
                print(f"🔐 {self.device_id}: Verificando checksum MD5...")
                time.sleep(1)
                
                # Simular cálculo de MD5
                calculated_md5 = hashlib.md5(f"firmware_{target_version}_{self.device_id}".encode()).hexdigest()
                
                # 10% de chance de falha no checksum (para teste)
                if random.random() < 0.1:
                    print(f"❌ {self.device_id}: Checksum não confere!")
                    self.send_ota_feedback(ota_id, "failed", "Checksum MD5 não confere")
                    self.ota_in_progress = False
                    return
                
                print(f"✅ {self.device_id}: Checksum MD5 verificado")
            
            # 5. Simular instalação
            print(f"💾 {self.device_id}: Instalando firmware...")
            time.sleep(2)
            
            # 15% de chance de falha na instalação (para teste)
            if random.random() < 0.15:
                print(f"❌ {self.device_id}: Falha na instalação!")
                self.send_ota_feedback(ota_id, "failed", "Erro ao gravar firmware no flash")
                self.ota_in_progress = False
                return
            
            # 6. Sucesso - atualizar versão
            old_version = self.firmware_version
            self.firmware_version = target_version
            
            print(f"✅ {self.device_id}: Firmware atualizado! {old_version} → {target_version}")
            self.send_ota_feedback(ota_id, "success", f"Firmware atualizado de {old_version} para {target_version}")
            
            # 7. Simular reinício (enviar status offline/online)
            time.sleep(1)
            self.send_device_status("restarting")
            time.sleep(2)
            self.uptime = 0  # Resetar uptime
            self.send_device_status("online")
            
            self.ota_in_progress = False
            print(f"🎉 {self.device_id}: OTA concluído com sucesso!")
            
        except Exception as e:
            print(f"❌ {self.device_id}: Erro durante OTA: {e}")
            self.send_ota_feedback(ota_id, "failed", f"Erro interno: {str(e)}")
            self.ota_in_progress = False

    def send_ota_feedback(self, ota_id, status, message):
        """Enviar feedback OTA via MQTT"""
        if not self.connected:
            return
        
        feedback = {
            "ota_id": ota_id,
            "device_id": self.device_id,
            "status": status,
            "message": message,
            "firmware_version": self.firmware_version,
            "timestamp": int(time.time() * 1000)
        }
        
        payload = json.dumps(feedback)
        self.mqtt_client.publish(self.topic_feedback, payload)
        print(f"📤 {self.device_id}: Feedback enviado - {status}")

    def send_ota_progress(self, ota_id, progress):
        """Enviar progresso OTA via MQTT"""
        if not self.connected:
            return
        
        progress_msg = {
            "ota_id": ota_id,
            "device_id": self.device_id,
            "status": "in_progress",
            "progress_percent": progress,
            "timestamp": int(time.time() * 1000)
        }
        
        payload = json.dumps(progress_msg)
        self.mqtt_client.publish(self.topic_feedback, payload)

    def send_device_status(self, status):
        """Enviar status do dispositivo via MQTT"""
        if not self.connected and status != "offline":
            return
        
        status_msg = {
            "device_id": self.device_id,
            "status": status,
            "firmware_version": self.firmware_version,
            "timestamp": int(time.time() * 1000)
        }
        
        payload = json.dumps(status_msg)
        self.mqtt_client.publish(self.topic_status, payload)

    def send_heartbeat(self):
        """Enviar heartbeat via MQTT"""
        if not self.connected:
            return
        
        # Simular variações nos valores
        self.uptime += 30000  # +30 segundos
        self.free_heap += random.randint(-1000, 1000)
        self.wifi_rssi += random.randint(-5, 5)
        
        # Manter valores realistas
        self.free_heap = max(20000, min(60000, self.free_heap))
        self.wifi_rssi = max(-90, min(-30, self.wifi_rssi))
        
        heartbeat = {
            "device_id": self.device_id,
            "device_type": self.device_type,
            "department": self.department,
            "firmware_version": self.firmware_version,
            "uptime": self.uptime,
            "free_heap": self.free_heap,
            "wifi_rssi": self.wifi_rssi,
            "ota_in_progress": self.ota_in_progress,
            "timestamp": int(time.time() * 1000)
        }
        
        payload = json.dumps(heartbeat)
        self.mqtt_client.publish(self.topic_status, payload)

    def heartbeat_loop(self):
        """Loop de heartbeat em thread separada"""
        while self.running:
            try:
                if self.connected:
                    self.send_heartbeat()
                time.sleep(30)  # Heartbeat a cada 30 segundos
            except Exception as e:
                print(f"❌ {self.device_id}: Erro no heartbeat: {e}")

# ========================================
# CLASSE PRINCIPAL DO SIMULADOR
# ========================================

class OTASimulator:
    def __init__(self):
        self.devices = []
        self.running = False

    def start(self):
        """Iniciar simulação de todos os dispositivos"""
        print("🚀 Iniciando Simulador OTA - Sistema MQTT IoT")
        print("=" * 50)
        
        # Criar e iniciar todos os dispositivos
        for config in DEVICE_CONFIGS:
            device = SimulatedESP32Device(config)
            device.start()
            self.devices.append(device)
            time.sleep(1)  # Pequeno delay entre dispositivos
        
        self.running = True
        print(f"\n✅ {len(self.devices)} dispositivos simulados iniciados!")
        print("\n📋 Comandos disponíveis:")
        print("   - Pressione 's' para mostrar status")
        print("   - Pressione 'q' para sair")
        print("   - Teste OTA pelo dashboard web")
        print("\n" + "=" * 50)

    def stop(self):
        """Parar simulação de todos os dispositivos"""
        print("\n⏹️ Parando simulador...")
        self.running = False
        
        for device in self.devices:
            device.stop()
        
        print("✅ Simulador parado!")

    def show_status(self):
        """Mostrar status de todos os dispositivos"""
        print("\n📊 Status dos Dispositivos Simulados")
        print("=" * 70)
        
        for device in self.devices:
            status = "🟢 Online" if device.connected else "🔴 Offline"
            ota_status = "🔄 OTA em progresso" if device.ota_in_progress else "⭕ Inativo"
            
            print(f"🆔 {device.device_id}")
            print(f"   Tipo: {device.device_type}")
            print(f"   Dept: {device.department}")
            print(f"   Status: {status}")
            print(f"   Firmware: v{device.firmware_version}")
            print(f"   OTA: {ota_status}")
            print(f"   Uptime: {device.uptime // 1000}s")
            print(f"   Heap: {device.free_heap} bytes")
            print(f"   RSSI: {device.wifi_rssi} dBm")
            print("-" * 40)

    def interactive_loop(self):
        """Loop interativo do simulador"""
        try:
            while self.running:
                command = input().strip().lower()
                
                if command == 'q':
                    break
                elif command == 's':
                    self.show_status()
                elif command.startswith('ota '):
                    # Comando para testar OTA específico
                    parts = command.split()
                    if len(parts) >= 2:
                        device_id = parts[1]
                        self.trigger_test_ota(device_id)
                else:
                    print("Comando não reconhecido. Use 's' para status ou 'q' para sair.")
        
        except KeyboardInterrupt:
            pass
        finally:
            self.stop()

    def trigger_test_ota(self, device_id):
        """Trigger manual de OTA para teste"""
        device = next((d for d in self.devices if d.device_id == device_id), None)
        if not device:
            print(f"❌ Dispositivo {device_id} não encontrado")
            return
        
        # Simular comando OTA manual
        test_command = {
            "command": "ota_update",
            "ota_id": f"test_{int(time.time())}",
            "firmware_version": "1.1.0",
            "firmware_url": f"http://firmware.iot.local/firmware/{device.device_type}/latest/firmware.bin",
            "checksum_md5": "a1b2c3d4e5f6789",
            "force_update": True,
            "timestamp": datetime.now().isoformat()
        }
        
        device.process_ota_command(json.dumps(test_command))
        print(f"🔄 OTA manual iniciado para {device_id}")

# ========================================
# FUNÇÃO PRINCIPAL
# ========================================

def main():
    """Função principal do simulador"""
    print("🔧 ESP32 OTA Simulator")
    print("Simulador de dispositivos ESP32 para teste do sistema OTA")
    print("")
    
    simulator = OTASimulator()
    
    try:
        simulator.start()
        simulator.interactive_loop()
    except Exception as e:
        print(f"❌ Erro no simulador: {e}")
    finally:
        simulator.stop()

if __name__ == "__main__":
    main() 