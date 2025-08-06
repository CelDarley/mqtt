#!/usr/bin/env python3
"""
Script simples para testar se mensagens MQTT estão chegando
"""

import paho.mqtt.client as mqtt
import time

# Configurações
MQTT_BROKER = "10.102.0.21"
MQTT_PORT = 1883
MQTT_TOPIC = "pmmg/1bpm/doc1"

def on_connect(client, userdata, flags, rc):
    print(f"Conectado ao broker MQTT! Código: {rc}")
    client.subscribe(MQTT_TOPIC)
    print(f"Inscrito no tópico: {MQTT_TOPIC}")

def on_message(client, userdata, msg):
    print(f"\n=== MENSAGEM RECEBIDA ===")
    print(f"Tópico: {msg.topic}")
    print(f"Mensagem: {msg.payload.decode('utf-8')}")
    print(f"Timestamp: {time.strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 30)

def main():
    print("=== TESTE MQTT ===")
    print(f"Broker: {MQTT_BROKER}:{MQTT_PORT}")
    print(f"Tópico: {MQTT_TOPIC}")
    print("=" * 30)
    
    client = mqtt.Client("teste_cliente")
    client.on_connect = on_connect
    client.on_message = on_message
    
    try:
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        print("Aguardando mensagens... (Ctrl+C para sair)")
        client.loop_forever()
    except KeyboardInterrupt:
        print("\nTeste finalizado.")
    except Exception as e:
        print(f"Erro: {e}")
    finally:
        client.disconnect()

if __name__ == "__main__":
    main() 