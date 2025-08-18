#!/usr/bin/env python3
"""
Script de teste para demonstrar o funcionamento da API MQTT
"""

import requests
import json
import time

# Configurações da API
API_BASE_URL = "http://localhost:8000/api/mqtt"

def test_create_topic():
    """Teste de criação de tópico"""
    print("=== TESTE: Criar Tópico ===")
    
    data = {
        "name": "teste/dispositivo",
        "description": "Tópico para testes"
    }
    
    response = requests.post(f"{API_BASE_URL}/topics", json=data)
    
    if response.status_code == 201:
        result = response.json()
        print("✅ Tópico criado com sucesso!")
        print(f"   ID: {result['data']['id']}")
        print(f"   Nome: {result['data']['name']}")
        return result['data']['name']
    else:
        print(f"❌ Erro ao criar tópico: {response.status_code}")
        print(response.text)
        return None

def test_list_topics():
    """Teste de listagem de tópicos"""
    print("\n=== TESTE: Listar Tópicos ===")
    
    response = requests.get(f"{API_BASE_URL}/topics")
    
    if response.status_code == 200:
        result = response.json()
        print("✅ Tópicos listados com sucesso!")
        print(f"   Total de tópicos: {len(result['data'])}")
        for topic in result['data']:
            print(f"   - {topic['name']} (ID: {topic['id']})")
    else:
        print(f"❌ Erro ao listar tópicos: {response.status_code}")
        print(response.text)

def test_send_message(topic_name):
    """Teste de envio de mensagem"""
    print(f"\n=== TESTE: Enviar Mensagem ===")
    
    data = {
        "topico": topic_name,
        "mensagem": "liberar"
    }
    
    response = requests.post(f"{API_BASE_URL}/send-message", json=data)
    
    if response.status_code == 200:
        result = response.json()
        print("✅ Mensagem enviada com sucesso!")
        print(f"   Tópico: {result['data']['topic']}")
        print(f"   Mensagem: {result['data']['message']}")
    else:
        print(f"❌ Erro ao enviar mensagem: {response.status_code}")
        print(response.text)

def test_send_message_invalid_topic():
    """Teste de envio de mensagem para tópico inexistente"""
    print(f"\n=== TESTE: Enviar Mensagem (Tópico Inexistente) ===")
    
    data = {
        "topico": "topico/inexistente",
        "mensagem": "liberar"
    }
    
    response = requests.post(f"{API_BASE_URL}/send-message", json=data)
    
    if response.status_code == 404:
        result = response.json()
        print("✅ Erro esperado - Tópico não existe!")
        print(f"   Mensagem: {result['message']}")
    else:
        print(f"❌ Comportamento inesperado: {response.status_code}")
        print(response.text)

def main():
    """Função principal"""
    print("🚀 INICIANDO TESTES DA API MQTT")
    print("=" * 50)
    
    # Teste 1: Listar tópicos existentes
    test_list_topics()
    
    # Teste 2: Criar novo tópico
    topic_name = test_create_topic()
    
    if topic_name:
        # Teste 3: Enviar mensagem para tópico válido
        test_send_message(topic_name)
        
        # Aguardar um pouco
        time.sleep(1)
        
        # Teste 4: Enviar mensagem para tópico inválido
        test_send_message_invalid_topic()
    
    print("\n" + "=" * 50)
    print("✅ TESTES CONCLUÍDOS!")
    print("\nPara testar o recebimento de mensagens, execute:")
    print("python3 exemplo_dispositivo.py")

if __name__ == "__main__":
    main() 