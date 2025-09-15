#!/bin/bash

# Script para adicionar firmware OTA - Sistema MQTT IoT
# Uso: ./adicionar_firmware.sh <caminho_firmware> <tipo_dispositivo> <versao>

echo "🚀 Adicionador de Firmware OTA - Sistema MQTT IoT"
echo "================================================"

# Verificar argumentos
if [ $# -lt 3 ]; then
    echo "❌ Uso: $0 <caminho_firmware> <tipo_dispositivo> <versao>"
    echo ""
    echo "📋 Exemplos:"
    echo "  $0 /home/user/firmware.bin sensor_de_temperatura v1.0.0"
    echo "  $0 ./meu_firmware.bin led_de_controle v2.1.0"
    echo ""
    echo "🔍 Tipos de dispositivos cadastrados:"
    curl -s "http://localhost:8000/api/mqtt/device-types" 2>/dev/null | jq -r '.data[]? | "  - \(.name | ascii_downcase | gsub(" "; "_") | gsub("ã"; "a") | gsub("ç"; "c")) (ID: \(.id))"' 2>/dev/null || echo "  ❌ Não foi possível listar (API offline)"
    exit 1
fi

FIRMWARE_FILE="$1"
DEVICE_TYPE="$2" 
VERSION="$3"

# Validações
echo "🔍 Validando arquivos..."

if [ ! -f "$FIRMWARE_FILE" ]; then
    echo "❌ Arquivo de firmware não encontrado: $FIRMWARE_FILE"
    exit 1
fi

if [ ! -r "$FIRMWARE_FILE" ]; then
    echo "❌ Sem permissão de leitura no arquivo: $FIRMWARE_FILE"
    exit 1
fi

FILE_SIZE=$(stat -c%s "$FIRMWARE_FILE")
if [ $FILE_SIZE -eq 0 ]; then
    echo "❌ Arquivo de firmware está vazio"
    exit 1
fi

echo "✅ Arquivo válido: $(basename $FIRMWARE_FILE) ($(numfmt --to=iec $FILE_SIZE))"

# Verificar se nginx está configurado
if [ ! -d "/var/www/firmware" ]; then
    echo "⚠️  Estrutura OTA não configurada. Configurando agora..."
    
    if [ -f "./setup-nginx-ota.sh" ]; then
        echo "🛠️  Executando configuração nginx..."
        sudo ./setup-nginx-ota.sh
    else
        echo "❌ Script setup-nginx-ota.sh não encontrado"
        echo "💡 Execute primeiro: sudo ./setup-nginx-ota.sh"
        exit 1
    fi
    
    if [ -f "./create-firmware-structure.sh" ]; then
        echo "📁 Criando estrutura de pastas..."
        sudo ./create-firmware-structure.sh
    fi
fi

# Criar estrutura de diretórios
FIRMWARE_DIR="/var/www/firmware/${DEVICE_TYPE}/${VERSION}"
echo "📁 Criando diretório: $FIRMWARE_DIR"
sudo mkdir -p "$FIRMWARE_DIR"

# Copiar firmware
echo "📦 Copiando firmware..."
sudo cp "$FIRMWARE_FILE" "$FIRMWARE_DIR/firmware.bin"

# Gerar checksum
echo "🔐 Gerando checksum MD5..."
cd "$FIRMWARE_DIR"
sudo md5sum firmware.bin > checksum.md5

# Criar version.json
echo "📄 Criando version.json..."
sudo tee "$FIRMWARE_DIR/version.json" > /dev/null << EOF
{
  "version": "$VERSION",
  "device_type": "$DEVICE_TYPE", 
  "release_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "description": "Firmware $VERSION para $DEVICE_TYPE",
  "file_size": $FILE_SIZE,
  "download_url": "http://firmware.iot.local/firmware/$DEVICE_TYPE/$VERSION/firmware.bin",
  "checksum_url": "http://firmware.iot.local/firmware/$DEVICE_TYPE/$VERSION/checksum.md5",
  "changelog": [
    "Nova versão de firmware",
    "Atualização via OTA"
  ]
}
EOF

# Ajustar permissões
echo "🔧 Ajustando permissões..."
sudo chown -R www-data:www-data "$FIRMWARE_DIR"
sudo chmod -R 644 "$FIRMWARE_DIR"/*
sudo chmod 755 "$FIRMWARE_DIR"

# Atualizar link latest
echo "🔗 Atualizando link 'latest'..."
DEVICE_DIR="/var/www/firmware/${DEVICE_TYPE}"
sudo rm -f "$DEVICE_DIR/latest"
sudo ln -sf "$VERSION" "$DEVICE_DIR/latest"

# Verificar resultado
echo ""
echo "✅ Firmware adicionado com sucesso!"
echo "=================================="
echo "📂 Localização: $FIRMWARE_DIR"
echo "📊 Tamanho: $(numfmt --to=iec $FILE_SIZE)"
echo "🔐 MD5: $(cat $FIRMWARE_DIR/checksum.md5 | cut -d' ' -f1)"
echo ""
echo "🌐 URLs disponíveis:"
echo "  📱 Download: http://firmware.iot.local/firmware/$DEVICE_TYPE/latest/firmware.bin"
echo "  📄 Versão:   http://firmware.iot.local/firmware/$DEVICE_TYPE/latest/version.json"
echo "  🔐 Checksum: http://firmware.iot.local/firmware/$DEVICE_TYPE/latest/checksum.md5"
echo ""

# Testar URLs
echo "🧪 Testando URLs..."

# Testar nginx
if curl -s -f "http://firmware.iot.local/api/version" > /dev/null; then
    echo "✅ Servidor nginx OTA funcionando"
else
    echo "❌ Servidor nginx não está respondendo"
    echo "💡 Verifique se nginx está rodando: sudo systemctl status nginx"
fi

# Testar arquivo
if curl -s -I "http://firmware.iot.local/firmware/$DEVICE_TYPE/latest/firmware.bin" | grep -q "200 OK"; then
    echo "✅ Firmware acessível via HTTP"
else
    echo "⚠️  Firmware pode não estar acessível via HTTP"
fi

# Testar version.json
if curl -s "http://firmware.iot.local/firmware/$DEVICE_TYPE/latest/version.json" | jq . > /dev/null 2>&1; then
    echo "✅ version.json válido"
else
    echo "⚠️  version.json pode ter problemas"
fi

echo ""
echo "🎯 Próximos passos:"
echo "  1. Acesse o dashboard web em http://localhost:8002"
echo "  2. Vá em 'Tipos de Dispositivo'"
echo "  3. Clique no botão 'Atualizar Firmware' do tipo correspondente"
echo "  4. Monitore os logs de atualização"
echo ""
echo "📋 Para listar todos os firmwares:"
echo "  ls -la /var/www/firmware/"
echo ""
echo "🔄 Para disparar OTA via API:"
echo "  curl -X POST \"http://localhost:8000/api/mqtt/device-types/ID/ota-update\" \\"
echo "    -H \"Content-Type: application/json\" \\"
echo "    -d '{\"target_devices\": \"all\"}'" 