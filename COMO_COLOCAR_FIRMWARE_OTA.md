# 📁 Como Colocar Firmware na Pasta para OTA

## 🎯 **Processo Completo em 4 Passos**

### **Passo 1: 🛠️ Configurar Estrutura (Uma vez só)**

```bash
# Ir para a pasta do projeto
cd /home/darley/mqtt

# Configurar nginx e estrutura de pastas (como root)
sudo ./setup-nginx-ota.sh
sudo ./create-firmware-structure.sh
```

### **Passo 2: 📂 Estrutura de Pastas**

A estrutura será criada em `/var/www/firmware/`:

```
/var/www/firmware/
├── sensor_de_temperatura/
│   ├── v1.0.0/
│   │   ├── firmware.bin      ← SEU ARQUIVO AQUI
│   │   ├── version.json      ← INFO DA VERSÃO
│   │   └── checksum.md5      ← VALIDAÇÃO
│   ├── v1.1.0/
│   └── latest → v1.0.0       ← LINK SIMBÓLICO
├── led_de_controle/
└── sensor_de_movimento/
```

### **Passo 3: 📋 Identificar Seu Tipo de Dispositivo**

Primeiro, veja quais tipos existem no sistema:

```bash
# Listar tipos de dispositivos cadastrados
curl -s "http://localhost:8000/api/mqtt/device-types" | jq '.data[] | {id: .id, name: .name, icon: .icon}'
```

**Exemplo de saída:**
```json
{
  "id": 1,
  "name": "Sensor de Temperatura",
  "icon": "🌡️"
}
{
  "id": 2, 
  "name": "LED de Controle",
  "icon": "💡"
}
```

### **Passo 4: 🚀 Adicionar Seu Firmware**

#### **4.1 - Criar pasta do tipo de dispositivo:**

```bash
# Exemplo para "Sensor de Temperatura" (ID 1)
DEVICE_TYPE="sensor_de_temperatura"
VERSION="v1.0.0"

# Criar estrutura de diretórios
sudo mkdir -p "/var/www/firmware/${DEVICE_TYPE}/${VERSION}"
```

#### **4.2 - Copiar seu arquivo .bin:**

```bash
# Copiar seu arquivo firmware.bin para a pasta
sudo cp /caminho/para/seu/firmware.bin "/var/www/firmware/${DEVICE_TYPE}/${VERSION}/firmware.bin"

# Ajustar permissões
sudo chown www-data:www-data "/var/www/firmware/${DEVICE_TYPE}/${VERSION}/firmware.bin"
sudo chmod 644 "/var/www/firmware/${DEVICE_TYPE}/${VERSION}/firmware.bin"
```

#### **4.3 - Criar arquivo version.json:**

```bash
# Criar arquivo de versão
sudo tee "/var/www/firmware/${DEVICE_TYPE}/${VERSION}/version.json" > /dev/null << EOF
{
  "version": "${VERSION}",
  "device_type": "${DEVICE_TYPE}",
  "release_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "description": "Firmware atualizado para ${DEVICE_TYPE}",
  "file_size": $(stat -c%s "/var/www/firmware/${DEVICE_TYPE}/${VERSION}/firmware.bin"),
  "download_url": "http://firmware.iot.local/firmware/${DEVICE_TYPE}/${VERSION}/firmware.bin",
  "checksum_url": "http://firmware.iot.local/firmware/${DEVICE_TYPE}/${VERSION}/checksum.md5",
  "changelog": [
    "Correções de bugs",
    "Melhorias de performance",
    "Novas funcionalidades"
  ]
}
EOF
```

#### **4.4 - Gerar checksum MD5:**

```bash
# Gerar checksum para validação
cd "/var/www/firmware/${DEVICE_TYPE}/${VERSION}"
sudo md5sum firmware.bin > checksum.md5

# Ajustar permissões dos arquivos
sudo chown www-data:www-data version.json checksum.md5
sudo chmod 644 version.json checksum.md5
```

#### **4.5 - Criar link "latest":**

```bash
# Remover link anterior (se existir)
sudo rm -f "/var/www/firmware/${DEVICE_TYPE}/latest"

# Criar link para versão mais recente
sudo ln -sf "${VERSION}" "/var/www/firmware/${DEVICE_TYPE}/latest"
```

## ✅ **Exemplo Prático Completo**

```bash
#!/bin/bash
# Script exemplo para adicionar firmware

# Configurações
DEVICE_TYPE="sensor_de_temperatura"
VERSION="v1.2.3"
FIRMWARE_FILE="/home/darley/meu_firmware.bin"

echo "🚀 Adicionando firmware ${VERSION} para ${DEVICE_TYPE}"

# 1. Criar estrutura
sudo mkdir -p "/var/www/firmware/${DEVICE_TYPE}/${VERSION}"

# 2. Copiar firmware
sudo cp "${FIRMWARE_FILE}" "/var/www/firmware/${DEVICE_TYPE}/${VERSION}/firmware.bin"

# 3. Criar version.json
sudo tee "/var/www/firmware/${DEVICE_TYPE}/${VERSION}/version.json" > /dev/null << EOF
{
  "version": "${VERSION}",
  "device_type": "${DEVICE_TYPE}",
  "release_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "description": "Nova versão com melhorias importantes",
  "file_size": $(stat -c%s "/var/www/firmware/${DEVICE_TYPE}/${VERSION}/firmware.bin"),
  "download_url": "http://firmware.iot.local/firmware/${DEVICE_TYPE}/${VERSION}/firmware.bin",
  "checksum_url": "http://firmware.iot.local/firmware/${DEVICE_TYPE}/${VERSION}/checksum.md5"
}
EOF

# 4. Gerar checksum
cd "/var/www/firmware/${DEVICE_TYPE}/${VERSION}"
sudo md5sum firmware.bin > checksum.md5

# 5. Ajustar permissões
sudo chown -R www-data:www-data "/var/www/firmware/${DEVICE_TYPE}/${VERSION}"
sudo chmod -R 644 "/var/www/firmware/${DEVICE_TYPE}/${VERSION}"/*

# 6. Atualizar link latest
sudo rm -f "/var/www/firmware/${DEVICE_TYPE}/latest"
sudo ln -sf "${VERSION}" "/var/www/firmware/${DEVICE_TYPE}/latest"

echo "✅ Firmware adicionado com sucesso!"
echo "🌐 URL: http://firmware.iot.local/firmware/${DEVICE_TYPE}/latest/firmware.bin"
```

## 🧪 **Testar o Firmware**

```bash
# 1. Verificar se o arquivo está acessível
curl -I "http://firmware.iot.local/firmware/sensor_de_temperatura/latest/firmware.bin"

# 2. Verificar version.json
curl "http://firmware.iot.local/firmware/sensor_de_temperatura/latest/version.json" | jq .

# 3. Verificar checksum
curl "http://firmware.iot.local/firmware/sensor_de_temperatura/latest/checksum.md5"

# 4. Testar OTA via API do sistema
curl -X POST "http://localhost:8000/api/mqtt/device-types/1/ota-update" \
  -H "Content-Type: application/json" \
  -d '{"target_devices": "all"}'
```

## 📝 **Resumo dos Arquivos Necessários**

Para cada firmware, você precisa de **3 arquivos**:

1. **`firmware.bin`** - Seu arquivo compilado do ESP32
2. **`version.json`** - Metadados da versão 
3. **`checksum.md5`** - Validação de integridade

## 🎯 **Próximos Passos**

1. ✅ Adicionar firmware seguindo este guia
2. 🌐 Testar URLs de acesso
3. 🔄 Usar botão OTA no frontend para disparar atualização
4. 📱 Verificar logs nos dispositivos ESP32

---
**💡 Dica:** Sempre teste a URL do firmware antes de disparar OTA em massa! 