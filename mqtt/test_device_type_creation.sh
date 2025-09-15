#!/bin/bash

# Script para testar criação de tipos de dispositivos
# ===================================================

echo "🧪 Testando criação de tipos de dispositivos"
echo "============================================"

# Cores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✅ $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ️ $1${NC}"; }

# Função para testar criação
test_device_type_creation() {
    local name="$1"
    local description="$2"
    local icon="$3"
    local specifications="$4"
    
    print_info "Testando: $name"
    
    RESPONSE=$(curl -s -X POST http://localhost:8000/api/mqtt/device-types \
        -H "Content-Type: application/json" \
        -d "{
            \"name\": \"$name\",
            \"description\": \"$description\",
            \"icon\": \"$icon\",
            \"specifications\": $specifications,
            \"is_active\": true
        }")
    
    SUCCESS=$(echo "$RESPONSE" | jq -r '.success // false' 2>/dev/null)
    
    if [ "$SUCCESS" = "true" ]; then
        ID=$(echo "$RESPONSE" | jq -r '.data.id')
        print_success "$name criado com ID: $ID"
        return 0
    else
        MESSAGE=$(echo "$RESPONSE" | jq -r '.message // "Erro desconhecido"' 2>/dev/null)
        ERRORS=$(echo "$RESPONSE" | jq -r '.errors // empty' 2>/dev/null)
        print_error "$name falhou: $MESSAGE"
        if [ "$ERRORS" != "" ]; then
            echo "   Erros: $ERRORS"
        fi
        return 1
    fi
}

# Verificar se backend está rodando
print_info "Verificando se backend está rodando..."
if ! curl -s http://localhost:8000/api/mqtt/device-types > /dev/null; then
    print_error "Backend não está rodando!"
    print_info "Execute: cd mqtt && php artisan serve"
    exit 1
fi
print_success "Backend está funcionando"

echo ""
print_info "Testando diferentes tipos de dispositivos:"
echo "=========================================="

# Teste 1: Tipo básico válido
test_device_type_creation \
    "Sensor de Teste $(date +%H%M%S)" \
    "Sensor para teste automatizado" \
    "🔬" \
    '{"frequencia": "5s", "precisao": "0.1C"}'

# Teste 2: Tipo com JSON simples
test_device_type_creation \
    "LED Teste $(date +%H%M%S)" \
    "LED para teste" \
    "💡" \
    '{"comando": "led_on"}'

# Teste 3: Tipo com especificações complexas
test_device_type_creation \
    "Sirene Teste $(date +%H%M%S)" \
    "Sirene para alertas de emergência" \
    "🔊" \
    '{"volume": "alto", "frequencia": "2000Hz", "comandos": {"ligar": "siren_on", "desligar": "siren_off"}}'

# Teste 4: Tipo sem especificações
test_device_type_creation \
    "Dispositivo Simples $(date +%H%M%S)" \
    "Dispositivo sem especificações técnicas" \
    "📱" \
    'null'

# Teste 5: Tentativa de duplicação (deve falhar)
print_info "Testando duplicação (deve falhar):"
test_device_type_creation \
    "Sensor de Teste $(date +%H%M%S)" \
    "Tentativa de duplicar nome" \
    "❌" \
    '{}'

echo ""
print_info "Listando tipos criados:"
echo "======================"
curl -s http://localhost:8000/api/mqtt/device-types | jq -r '.data[] | "🆔 \(.id) - \(.name) (\(.icon))"' | tail -5

echo ""
print_info "Testando validação de JSON inválido:"
echo "===================================="

# Teste de JSON inválido
INVALID_RESPONSE=$(curl -s -X POST http://localhost:8000/api/mqtt/device-types \
    -H "Content-Type: application/json" \
    -d '{
        "name": "Teste JSON Inválido",
        "description": "Teste com JSON malformado",
        "icon": "🚫",
        "specifications": {"teste": "valor sem aspas},
        "is_active": true
    }')

echo "Resposta JSON inválido:"
echo "$INVALID_RESPONSE" | jq . 2>/dev/null || echo "$INVALID_RESPONSE"

echo ""
print_success "Testes concluídos!"
print_info "💡 Dicas para o usuário:"
echo "========================"
echo "• Nomes devem ser únicos"
echo "• JSON deve ser válido (use aspas duplas)"
echo "• Ícone é opcional mas recomendado"
echo "• Especificações são opcionais"
echo ""
print_info "Exemplo de JSON válido:"
echo '{"comando": "led_on", "voltagem": "3.3V", "corrente": "20mA"}' 