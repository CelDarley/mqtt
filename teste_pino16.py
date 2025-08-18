#!/usr/bin/env python3
"""
Teste simples para o pino 16 (GPIO23)
"""

import RPi.GPIO as GPIO
import time

# Configuração
PIN_LED = 23  # GPIO23 (Pino 16)

def setup():
    """Configurar GPIO"""
    print("=== TESTE PINO 16 ===")
    print(f"LED: GPIO{PIN_LED} (Pino 16)")
    print("=" * 30)
    
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(PIN_LED, GPIO.OUT)
    
    print("GPIO configurado!")

def test_led():
    """Testar LED"""
    try:
        print("\n>>> Iniciando teste...")
        
        # Teste 1: Ligar por 3 segundos
        print("1. Ligando LED por 3 segundos...")
        GPIO.output(PIN_LED, GPIO.HIGH)
        time.sleep(3)
        
        # Teste 2: Desligar por 2 segundos
        print("2. Desligando LED por 2 segundos...")
        GPIO.output(PIN_LED, GPIO.LOW)
        time.sleep(2)
        
        # Teste 3: Piscar 5 vezes
        print("3. Piscando 5 vezes...")
        for i in range(5):
            print(f"   Piscada {i+1}/5")
            GPIO.output(PIN_LED, GPIO.HIGH)
            time.sleep(0.5)
            GPIO.output(PIN_LED, GPIO.LOW)
            time.sleep(0.5)
        
        print("\n>>> Teste concluído!")
        
    except KeyboardInterrupt:
        print("\n>>> Teste interrompido!")
    except Exception as e:
        print(f"\n>>> Erro: {e}")
    finally:
        GPIO.cleanup()
        print("GPIO limpo!")

if __name__ == "__main__":
    setup()
    test_led() 