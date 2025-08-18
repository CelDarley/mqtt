#!/usr/bin/env python3
"""
Script simples para testar LED piscando (versão simulada)
Use este script para verificar se as ligações estão corretas
Funciona em qualquer sistema (não precisa de Raspberry Pi)
"""

import time

# Configuração do pino
LED_PIN = 23  # Pino 16 (GPIO23) para o LED

class GPIO:
    """Simulação do GPIO para teste"""
    def __init__(self):
        self.led_state = False
    
    def setmode(self, mode):
        print(f"Configurando modo GPIO: {mode}")
    
    def setup(self, pin, mode):
        print(f"Configurando pino {pin} como {mode}")
    
    def output(self, pin, state):
        self.led_state = state
        status = "LIGADO" if state else "DESLIGADO"
        print(f"  LED {status} (Pino {pin})")
    
    def cleanup(self):
        print("GPIO limpo!")

def setup():
    """Configurar GPIO"""
    print("=== TESTE LED PISCANDO ===")
    print(f"Pino configurado: {LED_PIN}")
    print("=" * 30)
    
    # Criar instância simulada do GPIO
    gpio = GPIO()
    
    # Configurar modo GPIO
    gpio.setmode("BCM")
    
    # Configurar pino como saída
    gpio.setup(LED_PIN, "OUTPUT")
    
    print("GPIO configurado com sucesso!")
    print("Iniciando teste de piscada...")
    
    return gpio

def test_led():
    """Testar LED piscando"""
    gpio = setup()
    
    try:
        print("\n>>> Iniciando teste de piscada...")
        
        # Piscar 5 vezes
        for i in range(5):
            print(f"\nPiscada {i+1}/5")
            
            # Ligar LED
            gpio.output(LED_PIN, True)
            time.sleep(1)
            
            # Desligar LED
            gpio.output(LED_PIN, False)
            time.sleep(1)
        
        print("\n>>> Teste concluído!")
        print("Se o LED piscou 5 vezes, as ligações estão corretas!")
        print("\nPara testar no Raspberry Pi real:")
        print("1. Instale RPi.GPIO: sudo apt install python3-rpi.gpio")
        print("2. Execute: python3 teste_led.py")
        
    except KeyboardInterrupt:
        print("\n>>> Teste interrompido pelo usuário")
    except Exception as e:
        print(f"\n>>> Erro durante o teste: {e}")
    finally:
        # Limpeza
        gpio.cleanup()
        print("GPIO limpo!")

def main():
    """Função principal"""
    test_led()

if __name__ == "__main__":
    main() 