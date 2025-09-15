# 🍓 Raspberry Pi do Zero - Guia Completo de Configuração

## 📋 **O que você vai conseguir no final:**
- ✅ Raspberry Pi funcionando como WiFi Manager
- ✅ LED permanentemente aceso quando conectado à rede
- ✅ Reset por botão (10 segundos) para voltar ao estado inicial
- ✅ Reconexão automática após reinicialização
- ✅ Access Point "IOT-Zontec" para configuração
- ✅ Interface web para configuração de dispositivos IoT

---

## 🛠️ **PARTE 1: Hardware e Preparação**

### **📦 Hardware Necessário:**
```
✅ Raspberry Pi Zero W ou Pi 4
✅ MicroSD Card (mínimo 8GB, recomendado 16GB)
✅ Adaptador USB-WiFi (se Pi Zero sem WiFi)
✅ LED (qualquer cor)
✅ Resistor 220Ω ou 330Ω
✅ Botão push-button (normalmente aberto)
✅ Resistor 10kΩ (pull-up para botão)
✅ Protoboard e jumpers
✅ Fonte de alimentação micro-USB
```

### **🔌 Conexões GPIO:**
```
Raspberry Pi GPIO:
┌─────────────────────────────┐
│ GPIO 16 ──→ LED (+ resistor)│  # LED de status
│ GPIO 18 ──→ Botão           │  # Botão de controle
│ 3.3V ───→ Pull-up resistor │  # Alimentação do botão
│ GND ────→ LED e Botão       │  # Terra comum
└─────────────────────────────┘

Esquema do LED:
GPIO 16 ──[220Ω]──[LED]──GND

Esquema do Botão:
3.3V ──[10kΩ]──┬──GPIO 18
               │
             [Botão]
               │
              GND
```

---

## 💽 **PARTE 2: Instalação do Sistema Operacional**

### **1. Download e Gravação do Raspberry Pi OS:**

#### **Opção A - Raspberry Pi Imager (Recomendado):**
```bash
# No seu computador:
# 1. Baixe o Raspberry Pi Imager: https://rpi.org/imager
# 2. Execute o programa
# 3. Escolha: "Raspberry Pi OS Lite" (sem interface gráfica)
# 4. Selecione seu cartão SD
# 5. ANTES de gravar, clique na engrenagem (⚙️)
```

#### **Configurações Avançadas no Imager:**
```
✅ Habilitar SSH
   Usuário: darley
   Senha: raspberry123
   
✅ Configurar WiFi
   SSID: catena (ou sua rede WiFi)
   Senha: [sua senha do WiFi]
   País: BR
   
✅ Configurar localização
   Timezone: America/Sao_Paulo
   Keyboard: br
```

#### **Opção B - Manual (Linha de Comando):**
```bash
# Download da imagem
wget https://downloads.raspberrypi.org/raspios_lite_armhf/images/raspios_lite_armhf-2023-12-11/2023-12-11-raspios-bookworm-armhf-lite.img.xz

# Descompactar
unxz 2023-12-11-raspios-bookworm-armhf-lite.img.xz

# Gravar no SD (substitua /dev/sdX pelo seu cartão)
sudo dd if=2023-12-11-raspios-bookworm-armhf-lite.img of=/dev/sdX bs=4M status=progress
```

### **2. Configuração Inicial Pós-Gravação:**

#### **Habilitar SSH (se não fez no Imager):**
```bash
# Monte a partição boot do SD card
# Crie arquivo vazio para habilitar SSH
touch /media/seu_usuario/bootfs/ssh

# Criar arquivo de usuário
echo 'darley:$6$rounds=656000$YourSaltHere$HashedPassword' > /media/seu_usuario/bootfs/userconf.txt
```

#### **Configurar WiFi (se não fez no Imager):**
```bash
# Criar arquivo wpa_supplicant.conf
cat > /media/seu_usuario/bootfs/wpa_supplicant.conf << 'EOF'
country=BR
ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
update_config=1
ap_scan=1

network={
    ssid="catena"
    psk="SUA_SENHA_WIFI"
    key_mgmt=WPA-PSK
}
EOF
```

---

## 🚀 **PARTE 3: Primeira Inicialização**

### **1. Conectar e Acessar o Raspberry Pi:**
```bash
# Inserir SD card no Raspberry Pi e ligar
# Aguardar 2-3 minutos para boot completo

# Descobrir IP do Raspberry Pi
nmap -sn 192.168.0.0/24 | grep -B2 -A2 "Raspberry\|b8:27:eb"

# Conectar via SSH (IP será algo como 192.168.0.107)
ssh darley@192.168.0.107
# Senha: raspberry123
```

### **2. Configuração Inicial do Sistema:**
```bash
# Update do sistema
sudo apt update && sudo apt upgrade -y

# Instalar ferramentas essenciais
sudo apt install -y git curl wget vim nano htop

# Configurar timezone
sudo timedatectl set-timezone America/Sao_Paulo

# Verificar configuração
timedatectl
```

### **3. Configuração de Usuário e Permissões:**
```bash
# Adicionar usuário aos grupos necessários
sudo usermod -a -G gpio,spi,i2c,audio,video darley

# Configurar sudo sem senha para GPIO
echo 'darley ALL=(ALL) NOPASSWD: /usr/bin/systemctl, /usr/bin/hostapd, /usr/bin/dnsmasq, /sbin/ip, /usr/bin/wpa_cli' | sudo tee /etc/sudoers.d/darley-gpio

# Habilitar SSH permanentemente
sudo systemctl enable ssh
```

---

## 🐍 **PARTE 4: Configuração do Python e Dependências**

### **1. Instalar Python e Dependências:**
```bash
# Instalar Python 3 e pip
sudo apt install -y python3 python3-pip python3-venv

# Instalar bibliotecas do sistema
sudo apt install -y python3-rpi.gpio python3-flask

# Instalar dependências Python via pip
sudo pip3 install --break-system-packages flask flask-cors

# Verificar instalação
python3 -c "import RPi.GPIO; import flask; import flask_cors; print('✅ Todas as dependências instaladas')"
```

### **2. Configurar Ferramentas de Rede:**
```bash
# Instalar ferramentas de rede
sudo apt install -y hostapd dnsmasq iptables-persistent

# Parar serviços (serão gerenciados pelo script)
sudo systemctl stop hostapd
sudo systemctl stop dnsmasq
sudo systemctl disable hostapd
sudo systemctl disable dnsmasq
```

---

## 📁 **PARTE 5: Transferir e Configurar os Arquivos**

### **1. Criar Estrutura de Diretórios:**
```bash
# No Raspberry Pi, criar diretório
mkdir -p /home/darley/wifi_manager_deploy
cd /home/darley/wifi_manager_deploy
```

### **2. Transferir Arquivos do Computador:**
```bash
# No seu computador (no diretório mqtt/wifi_manager_deploy/):
scp raspberry_wifi_setup_no_gpio.py darley@192.168.0.107:/home/darley/wifi_manager_deploy/
scp wifi-manager.service darley@192.168.0.107:/home/darley/wifi_manager_deploy/
scp install_service.sh darley@192.168.0.107:/home/darley/wifi_manager_deploy/
scp uninstall_service.sh darley@192.168.0.107:/home/darley/wifi_manager_deploy/
scp install_flask_cors.sh darley@192.168.0.107:/home/darley/wifi_manager_deploy/
scp README_INSTRUCTIONS.md darley@192.168.0.107:/home/darley/wifi_manager_deploy/

# Ou usar o script de deploy automatizado:
./deploy_to_raspberry.sh 192.168.0.107
```

### **3. Configurar Permissões:**
```bash
# No Raspberry Pi
cd /home/darley/wifi_manager_deploy
chmod +x *.sh *.py
```

---

## ⚙️ **PARTE 6: Instalação do Serviço**

### **1. Instalar o WiFi Manager como Serviço:**
```bash
# No Raspberry Pi
cd /home/darley/wifi_manager_deploy
sudo ./install_service.sh
```

### **2. Verificar Instalação:**
```bash
# Verificar status do serviço
sudo systemctl status wifi-manager

# Ver logs
sudo journalctl -u wifi-manager -f

# Verificar se está rodando
sudo systemctl is-active wifi-manager
```

---

## 🔧 **PARTE 7: Configuração de Hardware**

### **1. Testar GPIO:**
```bash
# Teste rápido do LED
python3 -c "
import RPi.GPIO as GPIO
import time
GPIO.setmode(GPIO.BCM)
GPIO.setup(16, GPIO.OUT)
for i in range(5):
    GPIO.output(16, GPIO.HIGH)
    time.sleep(0.5)
    GPIO.output(16, GPIO.LOW)
    time.sleep(0.5)
GPIO.cleanup()
print('✅ LED testado com sucesso')
"

# Teste do botão
python3 -c "
import RPi.GPIO as GPIO
import time
GPIO.setmode(GPIO.BCM)
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)
print('Pressione o botão (Ctrl+C para sair)...')
try:
    while True:
        if GPIO.input(18) == GPIO.LOW:
            print('🔘 Botão pressionado!')
            time.sleep(0.5)
        time.sleep(0.1)
except KeyboardInterrupt:
    GPIO.cleanup()
    print('✅ Botão testado com sucesso')
"
```

---

## 🌐 **PARTE 8: Configuração de Rede**

### **1. Configurar Interface WiFi Dupla (se necessário):**
```bash
# Se usando dongle USB adicional, verificar interfaces
ip link show

# Deve mostrar:
# wlan0: WiFi interno (conexão com rede local)
# wlan1: Dongle USB (para Access Point)
```

### **2. Configurações de Rede:**
```bash
# Backup da configuração original
sudo cp /etc/dhcpcd.conf /etc/dhcpcd.conf.backup

# O script já gerencia as configurações de rede automaticamente
echo "✅ Configurações de rede serão gerenciadas pelo WiFi Manager"
```

---

## ✅ **PARTE 9: Teste Final e Validação**

### **1. Reiniciar e Testar:**
```bash
# Reiniciar o Raspberry Pi
sudo reboot

# Aguardar 2-3 minutos e reconectar
ssh darley@192.168.0.107

# Verificar se serviço iniciou automaticamente
sudo systemctl status wifi-manager
```

### **2. Teste de Funcionalidades:**

#### **Teste 1: LED e Status**
```bash
# Verificar se LED acende quando conectado à rede
# LED deve estar aceso fixo se conectado ao WiFi
```

#### **Teste 2: Access Point Manual**
```bash
# Pressionar botão físico rapidamente (< 2 segundos)
# LED deve começar a piscar rápido
# Rede "IOT-Zontec" deve aparecer nos dispositivos próximos
```

#### **Teste 3: Factory Reset**
```bash
# Pressionar e manter botão por 10+ segundos
# LED deve piscar rápido após reset
# Credenciais WiFi devem ser apagadas
```

#### **Teste 4: Interface Web**
```bash
# Conectar smartphone à rede "IOT-Zontec"
# Senha: iot123456
# Acessar: http://192.168.4.1:5000
# Deve aparecer interface de configuração
```

---

## 📱 **PARTE 10: Configuração do Sistema Backend (Opcional)**

### **Se quiser servidor completo no mesmo Raspberry Pi:**

```bash
# Instalar PHP e Laravel
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip composer

# Instalar MySQL/MariaDB
sudo apt install -y mariadb-server
sudo mysql_secure_installation

# Transferir projeto completo
scp -r mqtt/ darley@192.168.0.107:/home/darley/

# Configurar projeto
cd /home/darley/mqtt
./setup.sh
php create_admin_user.php
./start_servers.sh
```

---

## 🔍 **PARTE 11: Troubleshooting**

### **Problemas Comuns:**

#### **LED não funciona:**
```bash
# Verificar conexão GPIO
gpio readall
# GPIO 16 deve estar configurado como OUT
```

#### **Botão não responde:**
```bash
# Verificar estado do botão
gpio -g read 18
# Deve retornar 1 (não pressionado) ou 0 (pressionado)
```

#### **Serviço não inicia:**
```bash
# Ver logs detalhados
sudo journalctl -u wifi-manager -n 50

# Verificar permissões
ls -la /home/darley/wifi_manager_deploy/
```

#### **WiFi não conecta:**
```bash
# Verificar configuração WiFi
sudo wpa_cli status
iwgetid -r
```

#### **Access Point não aparece:**
```bash
# Verificar hostapd
sudo systemctl status hostapd
sudo journalctl -u hostapd -n 20
```

---

## 📊 **RESULTADO FINAL**

### **✅ O que você terá funcionando:**

#### **🔌 WiFi Manager Completo:**
- ✅ **LED aceso permanente** quando conectado à rede local
- ✅ **LED piscando rápido** quando AP ativo (aguardando configuração)
- ✅ **LED piscando lento** quando dispositivo conectado ao AP
- ✅ **Reset por botão** (10s) - volta ao estado inicial
- ✅ **Reconexão automática** após reinicialização
- ✅ **Monitoramento contínuo** de conexão

#### **📱 Interface Web:**
- ✅ **Captive Portal** em http://192.168.4.1:5000
- ✅ **Configuração WiFi** via smartphone
- ✅ **Criação de tópicos MQTT** (se backend configurado)
- ✅ **Interface responsiva** para mobile

#### **⚙️ Sistema Robusto:**
- ✅ **Serviço systemd** - inicia automaticamente
- ✅ **Logs estruturados** via journalctl
- ✅ **Restart automático** em caso de falha
- ✅ **Persistência de configurações**

---

## 🎯 **Credenciais e Informações Importantes**

### **🔑 Acesso SSH:**
```
Usuário: darley
Senha: raspberry123
IP: 192.168.0.107 (ou conforme sua rede)
```

### **📡 Access Point:**
```
Nome da Rede: IOT-Zontec
Senha: iot123456
IP do Raspberry: 192.168.4.1
Interface Web: http://192.168.4.1:5000
```

### **📋 Comandos Úteis:**

#### **🔧 Controle do Serviço:**
```bash
sudo systemctl status wifi-manager      # Ver status
sudo systemctl stop wifi-manager        # Parar
sudo systemctl start wifi-manager       # Iniciar
sudo systemctl restart wifi-manager     # Reiniciar
sudo systemctl disable wifi-manager     # Desabilitar inicialização automática
sudo systemctl enable wifi-manager      # Habilitar inicialização automática
```

#### **📊 Monitoramento e Logs:**
```bash
sudo journalctl -u wifi-manager -f      # Ver logs em tempo real
sudo journalctl -u wifi-manager -n 50   # Ver últimas 50 linhas de log
sudo journalctl -u wifi-manager --since "1 hour ago"  # Logs da última hora
```

#### **🗑️ Remoção/Reinstalação:**
```bash
sudo ./uninstall_service.sh             # Remover serviço completamente
sudo ./install_service.sh               # Reinstalar serviço
```

### **📂 Arquivos Importantes:**
```
/home/darley/wifi_manager_deploy/          # Diretório principal
/etc/systemd/system/wifi-manager.service   # Configuração do serviço
/home/pi/wifi_manager/known_networks.json  # Redes WiFi salvas
```

---

**🎉 Parabéns! Seu Raspberry Pi Zero está configurado e funcionando como um WiFi Manager completo!**

**💡 LED permanente = Dispositivo conectado e funcionando**  
**🔄 Reset de 10s = Voltar ao estado inicial para nova configuração**  
**📱 Interface web = Configuração fácil via smartphone** 