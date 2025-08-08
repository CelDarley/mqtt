#!/bin/bash

# Script para fazer upload dos arquivos para o servidor MQTT
# Servidor MQTT: 10.100.0.21
# Usuário: darley

echo "📤 Fazendo upload dos arquivos para o servidor MQTT..."

# Configurações
SERVIDOR_MQTT="10.100.0.21"
USUARIO_MQTT="darley"
SENHA_MQTT="yhvh77"
PASTA_DESTINO="/root/api-mqtt"

echo "📋 Configurações:"
echo "  - Servidor MQTT: $SERVIDOR_MQTT"
echo "  - Usuário: $USUARIO_MQTT"
echo "  - Pasta destino: $PASTA_DESTINO"

echo "📤 Fazendo upload dos arquivos..."
sshpass -p "$SENHA_MQTT" scp -o StrictHostKeyChecking=no -r \
    app/ \
    bootstrap/ \
    config/ \
    database/ \
    public/ \
    resources/ \
    routes/ \
    storage/ \
    vendor/ \
    artisan \
    composer.json \
    composer.lock \
    "$USUARIO_MQTT@$SERVIDOR_MQTT:$PASTA_DESTINO/"

echo "✅ Upload dos arquivos concluído!"
echo ""
echo "📋 Próximos passos:"
echo "1. Conecte ao servidor MQTT: ssh darley@10.100.0.21"
echo "2. Execute o script de deploy como root: sudo ./deploy_root_mqtt.sh"
echo ""
echo "🔧 Ou copie o script para o servidor:"
echo "   scp deploy_root_mqtt.sh darley@10.100.0.21:/home/darley/" 