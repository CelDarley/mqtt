#!/bin/bash

# Script para criar estrutura de pastas de firmware
# Baseado nos tipos de dispositivos do banco de dados

echo "📁 Criando estrutura de pastas para firmwares OTA"
echo "================================================"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Este script deve ser executado como root (sudo)"
    exit 1
fi

# Diretório base
FIRMWARE_DIR="/var/www/firmware"

echo "🗃️ Criando diretório base: $FIRMWARE_DIR"
mkdir -p $FIRMWARE_DIR

# Buscar tipos de dispositivos do banco
echo "🔍 Buscando tipos de dispositivos do banco de dados..."
DEVICE_TYPES=$(php artisan tinker --execute="
\$types = App\Models\DeviceType::all(['name']);
foreach(\$types as \$type) {
    echo str_replace([' ', 'ã', 'ç', 'á', 'é', 'í', 'ó', 'ú'], ['_', 'a', 'c', 'a', 'e', 'i', 'o', 'u'], strtolower(\$type->name)) . PHP_EOL;
}
")

if [ -z "$DEVICE_TYPES" ]; then
    echo "⚠️ Nenhum tipo de dispositivo encontrado no banco. Criando estrutura padrão..."
    DEVICE_TYPES="sensor_de_temperatura
sensor_de_umidade
led_de_controle
sensor_de_movimento
rele_de_controle
sensor_de_pressao
camera_de_monitoramento
sensor_de_vibracao
display_oled
sensor_de_qualidade_do_ar"
fi

echo "📋 Tipos de dispositivos encontrados:"
echo "$DEVICE_TYPES"
echo ""

# Criar estrutura para cada tipo
for device_type in $DEVICE_TYPES; do
    echo "📁 Criando estrutura para: $device_type"
    
    # Criar diretórios de versão
    mkdir -p "$FIRMWARE_DIR/$device_type/v1.0.0"
    mkdir -p "$FIRMWARE_DIR/$device_type/v1.1.0"
    
    # Criar link simbólico para 'latest'
    ln -sf v1.0.0 "$FIRMWARE_DIR/$device_type/latest"
    
    # Criar arquivo version.json para v1.0.0
    cat > "$FIRMWARE_DIR/$device_type/v1.0.0/version.json" << EOF
{
    "version": "1.0.0",
    "release_date": "$(date -I)",
    "device_type": "$device_type",
    "firmware_url": "http://firmware.iot.local/firmware/$device_type/latest/firmware.bin",
    "checksum_url": "http://firmware.iot.local/firmware/$device_type/latest/checksum.md5",
    "changelog": [
        "Versão inicial do firmware",
        "Funcionalidades básicas implementadas",
        "Conectividade MQTT configurada"
    ],
    "min_version": "1.0.0",
    "force_update": false,
    "size_bytes": 1048576,
    "esp32_chip": "ESP32",
    "arduino_version": "2.0.0"
}
EOF

    # Criar arquivo version.json para v1.1.0
    cat > "$FIRMWARE_DIR/$device_type/v1.1.0/version.json" << EOF
{
    "version": "1.1.0",
    "release_date": "$(date -I)",
    "device_type": "$device_type",
    "firmware_url": "http://firmware.iot.local/firmware/$device_type/latest/firmware.bin",
    "checksum_url": "http://firmware.iot.local/firmware/$device_type/latest/checksum.md5",
    "changelog": [
        "Correções de bugs da v1.0.0",
        "Melhorias na conectividade WiFi",
        "Otimização de consumo de energia",
        "Suporte a OTA implementado"
    ],
    "min_version": "1.0.0",
    "force_update": false,
    "size_bytes": 1100000,
    "esp32_chip": "ESP32",
    "arduino_version": "2.0.0"
}
EOF
    
    # Criar firmware de exemplo (arquivo vazio por enquanto)
    touch "$FIRMWARE_DIR/$device_type/v1.0.0/firmware.bin"
    touch "$FIRMWARE_DIR/$device_type/v1.1.0/firmware.bin"
    
    # Gerar checksums MD5
    echo "d41d8cd98f00b204e9800998ecf8427e" > "$FIRMWARE_DIR/$device_type/v1.0.0/checksum.md5"
    echo "d41d8cd98f00b204e9800998ecf8427e" > "$FIRMWARE_DIR/$device_type/v1.1.0/checksum.md5"
    
    # Criar README específico
    cat > "$FIRMWARE_DIR/$device_type/README.md" << EOF
# Firmware - $(echo $device_type | tr '_' ' ' | sed 's/\b\w/\U&/g')

## Versões Disponíveis

### v1.0.0 (Atual)
- Data: $(date -I)
- Funcionalidades básicas
- Conectividade MQTT

### v1.1.0 (Desenvolvimento)
- Data: $(date -I)
- Melhorias e correções
- Suporte OTA

## Estrutura

\`\`\`
$device_type/
├── v1.0.0/
│   ├── firmware.bin
│   ├── version.json
│   └── checksum.md5
├── v1.1.0/
│   ├── firmware.bin
│   ├── version.json
│   └── checksum.md5
├── latest -> v1.0.0
└── README.md
\`\`\`

## URLs de Acesso

- **Firmware Atual**: http://firmware.iot.local/firmware/$device_type/latest/firmware.bin
- **Versão Info**: http://firmware.iot.local/firmware/$device_type/latest/version.json
- **Checksum**: http://firmware.iot.local/firmware/$device_type/latest/checksum.md5

## Comandos úteis

\`\`\`bash
# Atualizar para nova versão
sudo ln -sf v1.1.0 $FIRMWARE_DIR/$device_type/latest

# Verificar checksum
md5sum $FIRMWARE_DIR/$device_type/latest/firmware.bin
\`\`\`
EOF

    echo "   ✅ $device_type criado"
done

# Ajustar permissões
echo ""
echo "🔧 Ajustando permissões..."
chown -R www-data:www-data $FIRMWARE_DIR
chmod -R 755 $FIRMWARE_DIR

# Criar arquivo de índice principal
cat > "$FIRMWARE_DIR/index.html" << EOF
<!DOCTYPE html>
<html>
<head>
    <title>Servidor OTA - Sistema MQTT IoT</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .device-type { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .version { background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 3px; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>🚀 Servidor OTA - Sistema MQTT IoT</h1>
    <p><strong>Data:</strong> $(date)</p>
    
    <h2>📱 Tipos de Dispositivos Disponíveis</h2>
EOF

# Adicionar links para cada tipo
for device_type in $DEVICE_TYPES; do
    cat >> "$FIRMWARE_DIR/index.html" << EOF
    <div class="device-type">
        <h3>$(echo $device_type | tr '_' ' ' | sed 's/\b\w/\U&/g')</h3>
        <div class="version">
            <strong>Versão Atual:</strong> 
            <a href="/firmware/$device_type/latest/version.json">version.json</a> |
            <a href="/firmware/$device_type/latest/firmware.bin">firmware.bin</a> |
            <a href="/firmware/$device_type/latest/checksum.md5">checksum.md5</a>
        </div>
        <div class="version">
            <strong>Histórico:</strong>
            <a href="/firmware/$device_type/">Todas as versões</a> |
            <a href="/firmware/$device_type/README.md">Documentação</a>
        </div>
    </div>
EOF
done

cat >> "$FIRMWARE_DIR/index.html" << EOF
    
    <h2>🔗 APIs Disponíveis</h2>
    <ul>
        <li><a href="/api/version">Status do Servidor</a></li>
        <li><a href="/api/device-types">Lista de Tipos</a></li>
        <li><a href="/status">Status nginx</a></li>
    </ul>
    
    <footer>
        <hr>
        <p><small>Sistema MQTT IoT - Servidor OTA | nginx</small></p>
    </footer>
</body>
</html>
EOF

echo ""
echo "✅ Estrutura de firmware criada com sucesso!"
echo "================================================"
echo "📁 Diretório base: $FIRMWARE_DIR"
echo "🌐 Página principal: http://firmware.iot.local/"
echo ""
echo "📊 Resumo criado:"
echo "$DEVICE_TYPES" | wc -l | xargs echo "   - Tipos de dispositivos:"
echo "   - 2 versões por tipo (v1.0.0, v1.1.0)"
echo "   - Links simbólicos 'latest' configurados"
echo "   - Arquivos version.json gerados"
echo "   - READMEs individuais criados"
echo ""
echo "🧪 Teste os endpoints:"
echo "   curl http://firmware.iot.local/api/version"
echo "   curl http://firmware.iot.local/firmware/sensor_de_temperatura/latest/version.json"

# Listar estrutura criada
echo ""
echo "📋 Estrutura criada:"
tree $FIRMWARE_DIR 2>/dev/null || find $FIRMWARE_DIR -type d | sort 