#!/usr/bin/env python3
"""
Exemplo de dispositivo IoT que recebe mensagens MQTT
Este código simula um dispositivo que controla um LED/GPIO
"""

import paho.mqtt.client as mqtt
import time
import json

# Configurações do MQTT
MQTT_BROKER = "10.102.0.21"  # IP do servidor MQTT
MQTT_PORT = 1883
MQTT_TOPIC = "pmmg/1bpm/doc1"  # Tópico padrão
MQTT_CLIENT_ID = "doca 1"

# Importar RPi.GPIO real
import RPi.GPIO as GPIO

# Configurar GPIO
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

# Função para configurar GPIO
def setup_gpio():
    GPIO.setup(23, GPIO.OUT)  # Pino 16 (GPIO23)
    GPIO.output(23, GPIO.LOW)  # Inicialmente desligado
    print("GPIO configurado: Pino 16 (GPIO23)")

# Função para controlar LED
def control_led(state):
    if state:
        GPIO.output(23, GPIO.HIGH)
        print("LED LIGADO (Pino 16)")
    else:
        GPIO.output(23, GPIO.LOW)
        print("LED DESLIGADO (Pino 16)")

# Função para limpar GPIO
def cleanup_gpio():
    GPIO.cleanup()
    print("GPIO limpo!")

def on_connect(client, userdata, flags, rc, properties=None):
    """Callback chamado quando conecta ao broker MQTT"""
    if rc == 0:
        print("Conectado ao broker MQTT com sucesso!")
        # Inscrever no tópico
        client.subscribe(MQTT_TOPIC)
        print(f"Inscrito no tópico: {MQTT_TOPIC}")
    else:
        print(f"Falha na conexão. Código: {rc}")

def on_message(client, userdata, msg):
    """Callback chamado quando recebe uma mensagem"""
    try:
        topic = msg.topic
        message = msg.payload.decode('utf-8')
        
        print(f"\n=== MENSAGEM RECEBIDA ===")
        print(f"Tópico: {topic}")
        print(f"Mensagem: {message}")
        print(f"Timestamp: {time.strftime('%Y-%m-%d %H:%M:%S')}")
        
        # Processar a mensagem
        if message.lower() == "liberar":
            print(">>> COMANDO: LIBERAR DISPOSITIVO")
            control_led(True)  # Ativar LED/GPIO no pino 16 (GPIO23)
            print(">>> Dispositivo LIBERADO!")
        elif message.lower() == "bloquear":
            print(">>> COMANDO: BLOQUEAR DISPOSITIVO")
            control_led(False)  # Desativar LED/GPIO no pino 16 (GPIO23)
            print(">>> Dispositivo BLOQUEADO!")
        elif message.lower() == "ligar":
            print(">>> COMANDO: LIGAR LED")
            control_led(True)
            print(">>> LED LIGADO!")
        elif message.lower() == "desligar":
            print(">>> COMANDO: DESLIGAR LED")
            control_led(False)
            print(">>> LED DESLIGADO!")
        else:
            print(f">>> Comando não reconhecido: {message}")
        
        print("=" * 30)
        
    except Exception as e:
        print(f"Erro ao processar mensagem: {e}")

def on_disconnect(client, userdata, rc, properties=None):
    """Callback chamado quando desconecta"""
    if rc != 0:
        print(f"Desconectado inesperadamente. Código: {rc}")
    else:
        print("Desconectado do broker MQTT")

def main():
    """Função principal"""
    print("=== DISPOSITIVO IoT MQTT ===")
    print(f"Broker: {MQTT_BROKER}:{MQTT_PORT}")
    print(f"Tópico: {MQTT_TOPIC}")
    print("=" * 30)
    
    # Configurar GPIO
    setup_gpio()
    
    # Criar cliente MQTT
    client = mqtt.Client(client_id=MQTT_CLIENT_ID)
    
    # Configurar callbacks
    client.on_connect = on_connect
    client.on_message = on_message
    client.on_disconnect = on_disconnect
    
    try:
        # Conectar ao broker
        print("Conectando ao broker MQTT...")
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        
        # Iniciar loop de eventos
        print("Iniciando loop de eventos...")
        print("Aguardando mensagens... (Ctrl+C para sair)")
        client.loop_forever()
        
    except KeyboardInterrupt:
        print("\nInterrompido pelo usuário")
    except Exception as e:
        print(f"Erro: {e}")
    finally:
        # Limpeza
        cleanup_gpio()
        client.disconnect()
        print("Dispositivo finalizado.")

if __name__ == "__main__":
    main() 