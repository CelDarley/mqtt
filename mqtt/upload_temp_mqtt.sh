#!/bin/bash

# Script para fazer upload dos arquivos para pasta temporária no servidor MQTT
# Servidor MQTT: 10.100.0.21
# Usuário: darley

echo "📤 Fazendo upload dos arquivos para pasta temporária no servidor MQTT..."

# Configurações
SERVIDOR_MQTT="10.100.0.21"
USUARIO_MQTT="darley"
SENHA_MQTT="yhvh77"
PASTA_TEMP="/home/darley/temp-api-mqtt"
PASTA_DESTINO="/root/api-mqtt"

echo "📋 Configurações:"
echo "  - Servidor MQTT: $SERVIDOR_MQTT"
echo "  - Usuário: $USUARIO_MQTT"
echo "  - Pasta temporária: $PASTA_TEMP"
echo "  - Pasta destino: $PASTA_DESTINO"

# Função para executar comandos no servidor MQTT
executar_mqtt() {
    sshpass -p "$SENHA_MQTT" ssh -o StrictHostKeyChecking=no "$USUARIO_MQTT@$SERVIDOR_MQTT" "$1"
}

echo "🗂️ Criando pasta temporária..."
executar_mqtt "mkdir -p $PASTA_TEMP"

echo "📤 Fazendo upload dos arquivos para pasta temporária..."
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
    "$USUARIO_MQTT@$SERVIDOR_MQTT:$PASTA_TEMP/"

echo "✅ Upload dos arquivos concluído!"
echo ""
echo "📋 Próximos passos:"
echo "1. Conecte ao servidor MQTT: ssh darley@10.100.0.21"
echo "2. Execute os comandos como root:"
echo "   sudo mkdir -p $PASTA_DESTINO"
echo "   sudo cp -r $PASTA_TEMP/* $PASTA_DESTINO/"
echo "   sudo chown -R darley:darley $PASTA_DESTINO"
echo "   sudo ./deploy_root_mqtt.sh"
echo ""
echo "🔧 Ou execute o script completo:"
echo "   sudo ./deploy_root_mqtt.sh" 