#!/usr/bin/env python3
"""
Script simples para testar LED piscando no pino 12
Use este script para verificar se as ligações estão corretas
"""

import RPi.GPIO as GPIO
import time

# Configuração do pino
LED_PIN = 23  # Pino 16 (GPIO23) para o LED

def setup():
    """Configurar GPIO"""
    print("=== TESTE LED PISCANDO ===")
    print(f"Pino configurado: {LED_PIN}")
    print("=" * 30)
    
    # Configurar modo GPIO
    GPIO.setmode(GPIO.BCM)
    
    # Configurar pino como saída
    GPIO.setup(LED_PIN, GPIO.OUT)
    
    print("GPIO configurado com sucesso!")
    print("Iniciando teste de piscada...")

def test_led():
    """Testar LED piscando"""
    try:
        print("\n>>> Iniciando teste de piscada...")
        
        # Piscar 5 vezes
        for i in range(5):
            print(f"Piscada {i+1}/5")
            
            # Ligar LED
            GPIO.output(LED_PIN, GPIO.HIGH)
            print("  LED LIGADO")
            time.sleep(1)
            
            # Desligar LED
            GPIO.output(LED_PIN, GPIO.LOW)
            print("  LED DESLIGADO")
            time.sleep(1)
        
        print("\n>>> Teste concluído!")
        print("Se o LED piscou 5 vezes, as ligações estão corretas!")
        
    except KeyboardInterrupt:
        print("\n>>> Teste interrompido pelo usuário")
    except Exception as e:
        print(f"\n>>> Erro durante o teste: {e}")
    finally:
        # Limpeza
        GPIO.cleanup()
        print("GPIO limpo!")

def main():
    """Função principal"""
    setup()
    test_led()

if __name__ == "__main__":
    main() 