# 🚀 Configuração OTA (Over-The-Air) - Sistema MQTT IoT

## 📋 Resumo das Implementações

✅ **Configuração nginx** - Servidor de arquivos para firmwares  
✅ **Estrutura de pastas** - Organização hierárquica de firmwares  
✅ **Endpoints backend** - API completa para gerenciar OTA  

## 🛠️ Passos para Configuração Completa

### 1. 🌐 Configurar nginx

```bash
# Executar como root
sudo ./setup-nginx-ota.sh
```

### 2. 📁 Criar Estrutura de Firmware

```bash
# Executar como root
sudo ./create-firmware-structure.sh
```

### 3. ⚙️ Configurar Ambiente

Adicionar no arquivo `.env`:

```env
# Firmware OTA Configuration
FIRMWARE_BASE_URL=http://firmware.iot.local
```

### 4. 🧪 Testar Configuração

```bash
# Testar servidor nginx
curl http://firmware.iot.local/api/version

# Testar endpoint backend
curl http://10.102.0.101:8000/api/mqtt/device-types/1/firmware-info

# Testar estrutura de firmware
curl http://firmware.iot.local/firmware/sensor_de_temperatura/latest/version.json
```

## 🔗 Endpoints OTA Implementados

### Device Types
- `POST /api/mqtt/device-types/{id}/ota-update` - Iniciar update OTA
- `GET /api/mqtt/device-types/{id}/firmware-info` - Info do firmware

### OTA Management
- `GET /api/mqtt/ota-updates` - Listar updates
- `GET /api/mqtt/ota-updates/{id}` - Status de update específico
- `GET /api/mqtt/ota-updates/{id}/logs` - Logs detalhados
- `POST /api/mqtt/ota-updates/{id}/cancel` - Cancelar update
- `POST /api/mqtt/ota-updates/{id}/device-feedback` - Feedback de dispositivo

### Statistics
- `GET /api/mqtt/ota-stats` - Estatísticas gerais

## 📊 Banco de Dados

### Nova Tabela: `ota_update_logs`
- Logs completos de atualizações OTA
- Resultados por dispositivo
- Estatísticas de sucesso/falha
- Metadata customizável

## 🌐 URLs do Servidor nginx

- **Homepage**: http://firmware.iot.local/
- **API Status**: http://firmware.iot.local/api/version
- **Firmware**: http://firmware.iot.local/firmware/{tipo}/latest/firmware.bin
- **Versão**: http://firmware.iot.local/firmware/{tipo}/latest/version.json

## 🔧 Arquivos Criados

### Scripts de Configuração
- `setup-nginx-ota.sh` - Configuração do nginx
- `create-firmware-structure.sh` - Estrutura de pastas
- `nginx-ota-config.conf` - Configuração nginx

### Backend
- `OtaUpdateLog` model
- `OtaService` service
- `OtaController` controller
- Migration para `ota_update_logs`

## 📱 Estrutura de Firmware

```
/var/www/firmware/
├── sensor_de_temperatura/
│   ├── v1.0.0/
│   │   ├── firmware.bin
│   │   ├── version.json
│   │   └── checksum.md5
│   ├── v1.1.0/
│   └── latest -> v1.0.0
├── led_de_controle/
└── sensor_de_movimento/
```

## 🔄 Fluxo de Atualização

1. **Trigger no Dashboard** → POST para endpoint OTA
2. **Backend verifica** → Firmware disponível + dispositivos
3. **Comandos MQTT** → Enviados para dispositivos específicos
4. **ESP32 baixa** → Firmware via HTTP do nginx
5. **Feedback** → Dispositivos reportam status via MQTT
6. **Logs** → Sistema registra resultados completos

## 🎯 Próximos Passos

1. **Interface Web** - Botão OTA no frontend
2. **Código ESP32** - Implementar cliente OTA
3. **Firmwares Reais** - Substituir arquivos de exemplo
4. **Integração MQTT** - Conectar com broker real
5. **Monitoramento** - Dashboard de updates em tempo real

## 🔒 Segurança

- ✅ Verificação MD5 de checksums
- ✅ Validação de tipos de arquivo
- ✅ Logs detalhados de acesso
- ⚠️ TODO: HTTPS para produção
- ⚠️ TODO: Autenticação para firmware downloads 