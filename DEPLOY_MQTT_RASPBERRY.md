# 🚀 Deploy MQTT para Raspberry Pi - Guia Rápido

## 📋 **Situação Atual**
- ✅ **Raspberry Pi conectado**: `192.168.0.107`
- ✅ **WiFi Manager básico funcionando**: Porta 5000
- ❌ **Falta integração MQTT**: Sem API `/api/mqtt/status`

---

## 🔧 **Deploy Rápido - Versão MQTT**

### **1. 📦 Preparar Arquivos para Deploy**
```bash
cd /home/darley/wifi_manager_deploy

# Verificar arquivos essenciais
ls -la raspberry_wifi_setup_no_gpio.py
ls -la install_mqtt_deps.sh
ls -la install_service.sh
```

### **2. 📤 Enviar Arquivos Atualizados**
```bash
# Parar serviço atual (se rodando)
ssh darley@192.168.0.107 "sudo systemctl stop wifi-manager 2>/dev/null || true"

# Enviar arquivo principal com MQTT
scp raspberry_wifi_setup_no_gpio.py darley@192.168.0.107:/home/pi/wifi_manager/

# Enviar scripts de instalação
scp install_mqtt_deps.sh darley@192.168.0.107:/home/pi/wifi_manager/
scp install_service.sh darley@192.168.0.107:/home/pi/wifi_manager/
```

### **3. 🔧 Instalar no Raspberry Pi**
```bash
# Conectar via SSH
ssh darley@192.168.0.107

# Ir para diretório
cd /home/pi/wifi_manager

# Dar permissões
sudo chmod +x *.sh *.py

# Instalar dependências MQTT
./install_mqtt_deps.sh

# Reinstalar serviço
./install_service.sh

# Verificar status
sudo systemctl status wifi-manager
```

### **4. ✅ Verificar Funcionamento**
```bash
# No seu computador, testar API MQTT
curl http://192.168.0.107:5000/api/mqtt/status

# Resposta esperada:
# {
#   "mqtt_connected": true,
#   "subscribed_topics": [...],
#   "device_status": "connected"
# }
```

---

## 🚨 **Se Não Conseguir SSH**

### **Opção 1: Deploy Automático**
```bash
cd /home/darley/wifi_manager_deploy
./deploy_to_raspberry.sh
```

### **Opção 2: Conectar Fisicamente**
1. **Conectar teclado e monitor** no Raspberry Pi
2. **Fazer login**: usuário `pi` ou `darley`
3. **Executar comandos** diretamente no terminal

---

## 📊 **Status Atual da Interface Web**

### **✅ Melhorias Implementadas:**
- 🔍 **Detecção inteligente**: Identifica dispositivos com/sem MQTT
- ⚠️ **Avisos claros**: Mostra status específico de cada dispositivo
- 💡 **Sugestões**: Orienta sobre como resolver problemas
- 🎨 **Indicadores visuais**: Cores diferentes para cada status

### **🎯 Status Possíveis na Interface:**
- ✅ **"Conectado (MQTT)"** - Verde: Tudo funcionando
- ⚠️ **"Sem MQTT"** - Amarelo: Dispositivo encontrado, mas sem MQTT
- ❌ **"Desconectado"** - Vermelho: Nenhum dispositivo encontrado

---

## 🔄 **Teste Imediato**

### **1. 🌐 Acesse a Interface:**
```
http://localhost:8001/topics
```

### **2. 🎮 Teste MQTT:**
1. Clique **"🎮 Testar MQTT"** em qualquer tópico
2. Observe o status:
   - Se **"⚠️ Sem MQTT"**: Precisa fazer deploy
   - Se **"❌ Desconectado"**: Raspberry Pi offline

### **3. 📱 Resultado Esperado Após Deploy:**
```
🔍 Verificando conexão com o dispositivo...
✅ Dispositivo encontrado com suporte MQTT completo!

Status: ✅ Conectado (MQTT)
```

---

## 🐛 **Troubleshooting**

### **❌ "SSH Connection Refused"**
```bash
# Verificar se SSH está ativo
ping 192.168.0.107

# Se não responder, device pode estar em AP mode
# Conectar na rede IOT-Zontec e tentar:
ssh darley@192.168.4.1
```

### **❌ "Permission Denied"**
```bash
# No Raspberry Pi:
sudo chown -R pi:pi /home/pi/wifi_manager
sudo chmod +x /home/pi/wifi_manager/*.py
sudo chmod +x /home/pi/wifi_manager/*.sh
```

### **❌ "Service Failed to Start"**
```bash
# Ver logs detalhados
sudo journalctl -u wifi-manager -f

# Verificar arquivo de serviço
sudo systemctl edit wifi-manager --full
```

---

## 🎉 **Resultado Final**

Após o deploy bem-sucedido:

✅ **Interface Web mostrará:**
- Status: **"✅ Conectado (MQTT)"**
- Comandos MQTT funcionando
- LED controlável via interface
- Status em tempo real

✅ **Comandos Disponíveis:**
- 💡 Ligar/Desligar LED
- 💫 LED Piscar  
- 📊 Solicitar Status
- 🔄 Factory Reset
- 🔧 Comandos Personalizados

**🚀 Sistema completamente funcional!** 