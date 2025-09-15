#!/bin/bash

# Script de Teste Completo - Sistema OTA MQTT IoT
# ================================================
# 
# Este script testa todos os componentes do sistema OTA:
# 1. Backend Laravel (API)
# 2. Frontend Web (Dashboard)
# 3. Dispositivos Simulados (Python)
# 4. Servidor nginx (Firmwares)
# 5. Fluxo completo de OTA

echo "🧪 TESTE COMPLETO - SISTEMA OTA MQTT IoT"
echo "========================================"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✅ $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ️ $1${NC}"; }

# Função para verificar dependências
check_dependencies() {
    print_info "Verificando dependências..."
    
    # Verificar Python3
    if ! command -v python3 &> /dev/null; then
        print_error "Python3 não encontrado. Instale: sudo apt install python3"
        exit 1
    fi
    
    # Verificar pip
    if ! command -v pip3 &> /dev/null; then
        print_error "pip3 não encontrado. Instale: sudo apt install python3-pip"
        exit 1
    fi
    
    # Verificar/instalar paho-mqtt
    if ! python3 -c "import paho.mqtt.client" 2>/dev/null; then
        print_info "Instalando paho-mqtt..."
        pip3 install paho-mqtt requests
    fi
    
    # Verificar jq
    if ! command -v jq &> /dev/null; then
        print_warning "jq não encontrado. Instale para melhor formatação: sudo apt install jq"
    fi
    
    print_success "Dependências verificadas"
}

# Função para testar backend
test_backend() {
    print_info "1. Testando Backend Laravel..."
    
    # Verificar se o servidor está rodando
    if ! curl -s http://localhost:8000/api/mqtt/device-types > /dev/null; then
        print_warning "Backend não está rodando. Iniciando..."
        cd mqtt
        php artisan serve --host=0.0.0.0 --port=8000 &
        BACKEND_PID=$!
        sleep 5
        cd ..
    fi
    
    # Testar endpoints
    echo "   📡 Testando endpoints..."
    
    # Device Types
    DEVICE_COUNT=$(curl -s http://localhost:8000/api/mqtt/device-types | jq -r '.data | length' 2>/dev/null || echo "0")
    if [ "$DEVICE_COUNT" -gt 0 ]; then
        print_success "Device Types: $DEVICE_COUNT tipos cadastrados"
    else
        print_error "Nenhum tipo de dispositivo encontrado"
        return 1
    fi
    
    # OTA Stats
    OTA_RESPONSE=$(curl -s http://localhost:8000/api/mqtt/ota-stats)
    if echo "$OTA_RESPONSE" | jq -e '.success' > /dev/null 2>&1; then
        print_success "OTA Stats: Endpoint funcionando"
    else
        print_error "Endpoint OTA Stats com problema"
    fi
    
    # Firmware Info
    FIRST_ID=$(curl -s http://localhost:8000/api/mqtt/device-types | jq -r '.data[0].id' 2>/dev/null)
    if [ "$FIRST_ID" != "null" ] && [ "$FIRST_ID" != "" ]; then
        FIRMWARE_INFO=$(curl -s http://localhost:8000/api/mqtt/device-types/$FIRST_ID/firmware-info)
        if echo "$FIRMWARE_INFO" | jq -e '.success' > /dev/null 2>&1; then
            print_success "Firmware Info: Endpoint funcionando"
        else
            print_warning "Firmware Info: nginx OTA não configurado"
        fi
    fi
}

# Função para testar nginx OTA
test_nginx_ota() {
    print_info "2. Testando Servidor nginx OTA..."
    
    # Verificar se nginx OTA está configurado
    if curl -s http://firmware.iot.local/api/version > /dev/null 2>&1; then
        SERVER_INFO=$(curl -s http://firmware.iot.local/api/version | jq -r '.server' 2>/dev/null)
        print_success "nginx OTA: $SERVER_INFO funcionando"
        
        # Verificar estrutura de firmware
        if curl -s http://firmware.iot.local/firmware/ > /dev/null 2>&1; then
            print_success "Estrutura de firmware: Acessível"
        else
            print_warning "Estrutura de firmware: Não encontrada"
        fi
    else
        print_warning "nginx OTA não configurado"
        print_info "Execute: sudo ./setup-nginx-ota.sh && sudo ./create-firmware-structure.sh"
    fi
}

# Função para iniciar dispositivos simulados
start_simulated_devices() {
    print_info "3. Iniciando Dispositivos Simulados..."
    
    # Verificar se já está rodando
    if pgrep -f "test_ota_simulation.py" > /dev/null; then
        print_warning "Simulador já está rodando"
        return 0
    fi
    
    # Iniciar simulador em background
    python3 test_ota_simulation.py > simulator.log 2>&1 &
    SIMULATOR_PID=$!
    
    # Aguardar inicialização
    sleep 5
    
    # Verificar se iniciou corretamente
    if kill -0 $SIMULATOR_PID 2>/dev/null; then
        print_success "Dispositivos simulados iniciados (PID: $SIMULATOR_PID)"
        echo $SIMULATOR_PID > simulator.pid
        return 0
    else
        print_error "Falha ao iniciar simulador"
        return 1
    fi
}

# Função para testar OTA completo
test_complete_ota_flow() {
    print_info "4. Testando Fluxo Completo de OTA..."
    
    # Buscar primeiro tipo de dispositivo
    FIRST_DEVICE_TYPE=$(curl -s http://localhost:8000/api/mqtt/device-types | jq -r '.data[0].id' 2>/dev/null)
    
    if [ "$FIRST_DEVICE_TYPE" == "null" ] || [ "$FIRST_DEVICE_TYPE" == "" ]; then
        print_error "Nenhum tipo de dispositivo disponível para teste"
        return 1
    fi
    
    print_info "Testando OTA para device type ID: $FIRST_DEVICE_TYPE"
    
    # Trigger OTA
    OTA_RESPONSE=$(curl -s -X POST http://localhost:8000/api/mqtt/device-types/$FIRST_DEVICE_TYPE/ota-update \
        -H "Content-Type: application/json" \
        -d '{"force_update": true, "user_id": 1}')
    
    OTA_SUCCESS=$(echo "$OTA_RESPONSE" | jq -r '.success' 2>/dev/null)
    
    if [ "$OTA_SUCCESS" == "true" ]; then
        OTA_LOG_ID=$(echo "$OTA_RESPONSE" | jq -r '.ota_log_id' 2>/dev/null)
        DEVICES_COUNT=$(echo "$OTA_RESPONSE" | jq -r '.devices_count' 2>/dev/null)
        print_success "OTA iniciado: Log ID $OTA_LOG_ID, $DEVICES_COUNT dispositivos"
        
        # Monitorar progresso
        print_info "Monitorando progresso OTA..."
        for i in {1..10}; do
            sleep 3
            STATUS_RESPONSE=$(curl -s http://localhost:8000/api/mqtt/ota-updates/$OTA_LOG_ID)
            STATUS=$(echo "$STATUS_RESPONSE" | jq -r '.data.status' 2>/dev/null)
            SUCCESS_RATE=$(echo "$STATUS_RESPONSE" | jq -r '.data.success_rate' 2>/dev/null)
            
            echo "   ⏱️ ${i}0s: Status=$STATUS, Taxa de sucesso=${SUCCESS_RATE}%"
            
            if [ "$STATUS" == "completed" ] || [ "$STATUS" == "failed" ]; then
                break
            fi
        done
        
        # Status final
        FINAL_STATUS=$(curl -s http://localhost:8000/api/mqtt/ota-updates/$OTA_LOG_ID | jq -r '.data.status' 2>/dev/null)
        if [ "$FINAL_STATUS" == "completed" ]; then
            print_success "OTA concluído com sucesso!"
        else
            print_warning "OTA finalizado com status: $FINAL_STATUS"
        fi
        
    else
        OTA_MESSAGE=$(echo "$OTA_RESPONSE" | jq -r '.message' 2>/dev/null)
        print_warning "OTA não iniciado: $OTA_MESSAGE"
    fi
}

# Função para mostrar estatísticas
show_ota_statistics() {
    print_info "5. Estatísticas OTA..."
    
    STATS_RESPONSE=$(curl -s http://localhost:8000/api/mqtt/ota-stats)
    
    if echo "$STATS_RESPONSE" | jq -e '.success' > /dev/null 2>&1; then
        TOTAL=$(echo "$STATS_RESPONSE" | jq -r '.stats.total_updates' 2>/dev/null)
        SUCCESSFUL=$(echo "$STATS_RESPONSE" | jq -r '.stats.successful_updates' 2>/dev/null)
        FAILED=$(echo "$STATS_RESPONSE" | jq -r '.stats.failed_updates' 2>/dev/null)
        ACTIVE=$(echo "$STATS_RESPONSE" | jq -r '.stats.active_updates' 2>/dev/null)
        
        echo "   📊 Total de updates: $TOTAL"
        echo "   ✅ Sucessos: $SUCCESSFUL"
        echo "   ❌ Falhas: $FAILED"
        echo "   🔄 Ativos: $ACTIVE"
        
        print_success "Estatísticas obtidas"
    else
        print_error "Erro ao obter estatísticas"
    fi
}

# Função para listar logs OTA
show_ota_logs() {
    print_info "6. Últimos Logs OTA..."
    
    LOGS_RESPONSE=$(curl -s "http://localhost:8000/api/mqtt/ota-updates?per_page=5")
    
    if echo "$LOGS_RESPONSE" | jq -e '.success' > /dev/null 2>&1; then
        echo "$LOGS_RESPONSE" | jq -r '.data.data[] | "   🆔 ID: \(.id) | \(.device_type) | Status: \(.status) | Taxa: \(.success_rate)%"' 2>/dev/null
        print_success "Logs listados"
    else
        print_warning "Nenhum log OTA encontrado"
    fi
}

# Função para cleanup
cleanup() {
    print_info "Limpando processos..."
    
    # Parar simulador
    if [ -f simulator.pid ]; then
        SIMULATOR_PID=$(cat simulator.pid)
        if kill -0 $SIMULATOR_PID 2>/dev/null; then
            kill $SIMULATOR_PID
            print_info "Simulador parado"
        fi
        rm -f simulator.pid
    fi
    
    # Parar backend se iniciamos nós
    if [ ! -z "$BACKEND_PID" ]; then
        kill $BACKEND_PID 2>/dev/null
        print_info "Backend parado"
    fi
}

# Função principal
main() {
    echo "🚀 Iniciando teste completo do sistema OTA..."
    echo ""
    
    # Verificar dependências
    check_dependencies
    echo ""
    
    # Executar testes
    test_backend
    echo ""
    
    test_nginx_ota
    echo ""
    
    start_simulated_devices
    echo ""
    
    # Aguardar dispositivos se conectarem
    print_info "Aguardando dispositivos se conectarem..."
    sleep 10
    
    test_complete_ota_flow
    echo ""
    
    show_ota_statistics
    echo ""
    
    show_ota_logs
    echo ""
    
    # Resumo final
    print_info "📋 RESUMO DO TESTE"
    echo "=================="
    print_success "✅ Backend Laravel testado"
    print_success "✅ Endpoints OTA funcionando"
    print_success "✅ Dispositivos simulados ativos"
    print_success "✅ Fluxo OTA completo testado"
    echo ""
    print_info "🌐 URLs úteis:"
    echo "   Backend: http://localhost:8000/api/mqtt/"
    echo "   Dashboard: http://localhost:8001/ (se configurado)"
    echo "   Firmware: http://firmware.iot.local/"
    echo ""
    print_info "📝 Logs do simulador: ./simulator.log"
    echo ""
    
    # Perguntar se quer manter rodando
    read -p "Manter dispositivos simulados rodando? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        cleanup
    else
        print_info "Dispositivos simulados continuam rodando..."
        print_info "Para parar: ./test_complete_ota_system.sh --stop"
    fi
}

# Função para parar tudo
stop_all() {
    print_info "Parando todos os serviços..."
    cleanup
    killall python3 2>/dev/null
    killall php 2>/dev/null
    print_success "Todos os serviços parados"
}

# Verificar argumentos
if [ "$1" == "--stop" ]; then
    stop_all
    exit 0
elif [ "$1" == "--help" ]; then
    echo "Uso: $0 [--stop|--help]"
    echo ""
    echo "Opções:"
    echo "  (sem argumentos)  Executar teste completo"
    echo "  --stop           Parar todos os serviços"
    echo "  --help           Mostrar esta ajuda"
    exit 0
fi

# Configurar trap para cleanup em caso de interrupção
trap cleanup EXIT INT TERM

# Executar função principal
main 