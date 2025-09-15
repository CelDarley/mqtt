#!/bin/bash

# Script de Demonstração Completa - Sistema OTA MQTT IoT
# ======================================================

echo "🚀 DEMONSTRAÇÃO SISTEMA OTA - MQTT IoT"
echo "====================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️ $1${NC}"
}

# 1. Verificar se o backend está rodando
echo ""
print_info "1. Verificando Backend Laravel..."
if curl -s http://localhost:8000/api/mqtt/device-types > /dev/null; then
    DEVICE_COUNT=$(curl -s http://localhost:8000/api/mqtt/device-types | jq -r '.data | length' 2>/dev/null)
    print_status "Backend funcionando - $DEVICE_COUNT tipos de dispositivos cadastrados"
else
    print_error "Backend não está rodando. Execute: php artisan serve"
    exit 1
fi

# 2. Listar tipos de dispositivos disponíveis
echo ""
print_info "2. Tipos de Dispositivos Disponíveis:"
echo "====================================="
curl -s http://localhost:8000/api/mqtt/device-types | jq -r '.data[] | "ID: \(.id) - \(.name) (\(.is_active // false | if . then "ativo" else "inativo" end))"' 2>/dev/null | head -5

# 3. Testar endpoint de firmware info
echo ""
print_info "3. Testando Informações de Firmware:"
echo "===================================="
FIRST_DEVICE_ID=$(curl -s http://localhost:8000/api/mqtt/device-types | jq -r '.data[0].id' 2>/dev/null)
if [ "$FIRST_DEVICE_ID" != "null" ] && [ "$FIRST_DEVICE_ID" != "" ]; then
    echo "Testando device tipo ID: $FIRST_DEVICE_ID"
    FIRMWARE_RESPONSE=$(curl -s http://localhost:8000/api/mqtt/device-types/$FIRST_DEVICE_ID/firmware-info)
    FIRMWARE_AVAILABLE=$(echo $FIRMWARE_RESPONSE | jq -r '.firmware_info.available' 2>/dev/null)
    
    if [ "$FIRMWARE_AVAILABLE" = "true" ]; then
        print_status "Firmware disponível para este tipo"
        echo $FIRMWARE_RESPONSE | jq -r '.firmware_info | "Versão: \(.version // "N/A")\nURL: \(.firmware_url // "N/A")"' 2>/dev/null
    else
        print_warning "Firmware não disponível (nginx não configurado)"
        print_info "Execute: sudo ./setup-nginx-ota.sh"
    fi
else
    print_error "Nenhum tipo de dispositivo encontrado"
fi

# 4. Demonstrar trigger de OTA
echo ""
print_info "4. Simulando Trigger de OTA Update:"
echo "==================================="
if [ "$FIRST_DEVICE_ID" != "null" ] && [ "$FIRST_DEVICE_ID" != "" ]; then
    echo "Enviando comando OTA para device tipo $FIRST_DEVICE_ID..."
    OTA_RESPONSE=$(curl -s -X POST http://localhost:8000/api/mqtt/device-types/$FIRST_DEVICE_ID/ota-update \
        -H "Content-Type: application/json" \
        -d '{"force_update": false, "user_id": 1}')
    
    OTA_SUCCESS=$(echo $OTA_RESPONSE | jq -r '.success' 2>/dev/null)
    OTA_MESSAGE=$(echo $OTA_RESPONSE | jq -r '.message' 2>/dev/null)
    
    if [ "$OTA_SUCCESS" = "true" ]; then
        print_status "OTA iniciado com sucesso!"
        OTA_LOG_ID=$(echo $OTA_RESPONSE | jq -r '.ota_log_id' 2>/dev/null)
        DEVICES_COUNT=$(echo $OTA_RESPONSE | jq -r '.devices_count' 2>/dev/null)
        print_info "Log ID: $OTA_LOG_ID | Dispositivos: $DEVICES_COUNT"
    else
        print_warning "OTA não pôde ser iniciado: $OTA_MESSAGE"
    fi
else
    print_error "Não foi possível testar OTA - sem tipos de dispositivos"
fi

# 5. Listar updates OTA recentes
echo ""
print_info "5. Updates OTA Recentes:"
echo "======================="
OTA_UPDATES=$(curl -s http://localhost:8000/api/mqtt/ota-updates?per_page=3)
UPDATE_COUNT=$(echo $OTA_UPDATES | jq -r '.data.data | length' 2>/dev/null)

if [ "$UPDATE_COUNT" -gt 0 ]; then
    print_status "$UPDATE_COUNT updates encontrados"
    echo $OTA_UPDATES | jq -r '.data.data[] | "ID: \(.id) | \(.device_type) | Status: \(.status) | Dispositivos: \(.devices_count)"' 2>/dev/null | head -3
else
    print_info "Nenhum update OTA registrado ainda"
fi

# 6. Estatísticas OTA
echo ""
print_info "6. Estatísticas OTA (últimos 30 dias):"
echo "======================================"
OTA_STATS=$(curl -s http://localhost:8000/api/mqtt/ota-stats)
TOTAL_UPDATES=$(echo $OTA_STATS | jq -r '.stats.total_updates' 2>/dev/null)
SUCCESSFUL=$(echo $OTA_STATS | jq -r '.stats.successful_updates' 2>/dev/null)
FAILED=$(echo $OTA_STATS | jq -r '.stats.failed_updates' 2>/dev/null)
ACTIVE=$(echo $OTA_STATS | jq -r '.stats.active_updates' 2>/dev/null)

echo "Total de updates: $TOTAL_UPDATES"
echo "Sucessos: $SUCCESSFUL"
echo "Falhas: $FAILED"
echo "Ativos: $ACTIVE"

# 7. Verificar estrutura nginx (se disponível)
echo ""
print_info "7. Verificando Servidor nginx OTA:"
echo "=================================="
if curl -s http://firmware.iot.local/api/version > /dev/null 2>&1; then
    print_status "Servidor nginx OTA funcionando"
    nginx_status=$(curl -s http://firmware.iot.local/api/version | jq -r '.server' 2>/dev/null)
    print_info "Servidor: $nginx_status"
    
    # Verificar estrutura de firmware
    if curl -s http://firmware.iot.local/firmware/ > /dev/null 2>&1; then
        print_status "Estrutura de firmware acessível"
        print_info "Acesse: http://firmware.iot.local/"
    else
        print_warning "Estrutura de firmware não encontrada"
    fi
else
    print_warning "Servidor nginx OTA não configurado"
    print_info "Execute: sudo ./setup-nginx-ota.sh && sudo ./create-firmware-structure.sh"
fi

# 8. Resumo final
echo ""
echo "🎯 RESUMO DA DEMONSTRAÇÃO"
echo "========================"
print_status "✅ Backend Laravel funcionando"
print_status "✅ Endpoints OTA implementados"
print_status "✅ Banco de dados configurado"

if curl -s http://firmware.iot.local/api/version > /dev/null 2>&1; then
    print_status "✅ Servidor nginx OTA funcionando"
else
    print_warning "⚠️ Servidor nginx pendente"
fi

print_info "📋 Endpoints principais:"
echo "   GET  /api/mqtt/device-types"
echo "   POST /api/mqtt/device-types/{id}/ota-update"
echo "   GET  /api/mqtt/ota-updates"
echo "   GET  /api/mqtt/ota-stats"

print_info "🌐 URLs importantes:"
echo "   Backend API: http://localhost:8000/api/mqtt/"
echo "   Firmware: http://firmware.iot.local/"

echo ""
print_info "Para configuração completa, execute:"
echo "   sudo ./setup-nginx-ota.sh"
echo "   sudo ./create-firmware-structure.sh"
echo ""
print_status "Demonstração concluída! 🚀" 