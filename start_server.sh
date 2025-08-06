#!/bin/bash

# Script para iniciar o servidor API MQTT
# IP: 10.102.0.21
# Usuário: darley
# Senha: yhvh77

echo "🚀 Iniciando API MQTT no IP 10.102.0.21..."

# Verificar se o IP está configurado
if ! ip addr show eno1 | grep -q "10.102.0.21"; then
    echo "⚠️  Configurando IP 10.102.0.21..."
    sudo ip addr add 10.102.0.21/24 dev eno1
fi

# Verificar se o Mosquitto está rodando
if ! systemctl is-active --quiet mosquitto; then
    echo "⚠️  Iniciando Mosquitto..."
    sudo systemctl start mosquitto
fi

# Verificar se o MySQL está rodando
if ! systemctl is-active --quiet mysql; then
    echo "⚠️  Iniciando MySQL..."
    sudo systemctl start mysql
fi

# Parar servidor anterior se estiver rodando
pkill -f "php artisan serve" 2>/dev/null

# Aguardar um pouco
sleep 2

# Iniciar o servidor Laravel
echo "✅ Iniciando servidor Laravel em http://10.102.0.21:8000"
echo "📋 Endpoints disponíveis:"
echo "   - POST http://10.102.0.21:8000/api/mqtt/topics"
echo "   - GET  http://10.102.0.21:8000/api/mqtt/topics"
echo "   - POST http://10.102.0.21:8000/api/mqtt/send-message"
echo ""
echo "🛑 Pressione Ctrl+C para parar o servidor"
echo ""

php artisan serve --host=10.102.0.21 --port=8000 