#!/bin/bash

# Script de teste para API MQTT no IP 10.102.0.21
# Usuário: darley
# Senha: yhvh77

API_BASE_URL="http://10.102.0.21:8000/api/mqtt"

echo "🧪 TESTANDO API MQTT NO IP 10.102.0.21"
echo "=================================================="

# Teste 1: Listar tópicos
echo "1️⃣  Testando listagem de tópicos..."
response=$(curl -s -X GET "$API_BASE_URL/topics" -H "Accept: application/json")
if echo "$response" | grep -q "success.*true"; then
    echo "✅ Listagem de tópicos: OK"
    echo "$response" | jq '.' 2>/dev/null || echo "$response"
else
    echo "❌ Erro na listagem de tópicos"
    echo "$response"
fi

echo ""

# Teste 2: Criar tópico
echo "2️⃣  Testando criação de tópico..."
timestamp=$(date +%s)
topic_name="teste/ip_$timestamp"
data="{\"name\": \"$topic_name\", \"description\": \"Teste no IP 10.102.0.21\"}"

response=$(curl -s -X POST "$API_BASE_URL/topics" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "$data")

if echo "$response" | grep -q "success.*true"; then
    echo "✅ Criação de tópico: OK"
    echo "$response" | jq '.' 2>/dev/null || echo "$response"
else
    echo "❌ Erro na criação de tópico"
    echo "$response"
fi

echo ""

# Teste 3: Enviar mensagem
echo "3️⃣  Testando envio de mensagem..."
data="{\"topico\": \"$topic_name\", \"mensagem\": \"liberar\"}"

response=$(curl -s -X POST "$API_BASE_URL/send-message" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "$data")

if echo "$response" | grep -q "success.*true"; then
    echo "✅ Envio de mensagem: OK"
    echo "$response" | jq '.' 2>/dev/null || echo "$response"
else
    echo "❌ Erro no envio de mensagem"
    echo "$response"
fi

echo ""

# Teste 4: Enviar mensagem para tópico inexistente
echo "4️⃣  Testando envio para tópico inexistente..."
data="{\"topico\": \"topico/inexistente\", \"mensagem\": \"liberar\"}"

response=$(curl -s -X POST "$API_BASE_URL/send-message" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "$data")

if echo "$response" | grep -q "Tópico não existe"; then
    echo "✅ Validação de tópico inexistente: OK"
    echo "$response" | jq '.' 2>/dev/null || echo "$response"
else
    echo "❌ Erro na validação de tópico inexistente"
    echo "$response"
fi

echo ""
echo "=================================================="
echo "✅ TESTES CONCLUÍDOS!"
echo ""
echo "📋 Informações do servidor:"
echo "   IP: 10.102.0.21"
echo "   Porta: 8000"
echo "   Usuário: darley"
echo "   Senha: yhvh77"
echo ""
echo "🔗 URLs dos endpoints:"
echo "   - POST http://10.102.0.21:8000/api/mqtt/topics"
echo "   - GET  http://10.102.0.21:8000/api/mqtt/topics"
echo "   - POST http://10.102.0.21:8000/api/mqtt/send-message"
echo ""
echo "📖 Para mais informações, consulte:"
echo "   - README.md"
echo "   - DOCUMENTACAO.md" 