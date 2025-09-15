# 🚀 Roteiro Completo de Uso do Sistema IoT

## 📋 Visão Geral
Este guia detalha todo o processo de configuração e uso do sistema IoT, desde a primeira conexão até o envio de mensagens MQTT.

---

## 🔧 Pré-requisitos

### Hardware Necessário
- **ESP32** com firmware carregado
- **LEDs indicadores** conectados aos pinos configurados
- **Rede WiFi** disponível
- **Servidor backend** rodando (Laravel API)

### Software Necessário
- **Interface web** de gerenciamento rodando
- **Backend API** ativo na porta 8000
- **Broker MQTT** configurado

---

## 📶 PASSO 1: Conexão Inicial ao AP

### 1.1 Ligando o ESP32
1. **Conecte** o ESP32 à alimentação
2. **Aguarde** 3-5 segundos para inicialização
3. **Observe** os LEDs indicadores:
   - LED principal piscando rápido = Modo AP ativo
   - LED MQTT desligado = Aguardando configuração

### 1.2 Conectando ao Access Point
1. **Abra** as configurações WiFi do seu dispositivo (celular/computador)
2. **Procure** pela rede: `IOT-Zontec`
3. **Conecte-se** à rede (sem senha)
4. **Aguarde** a conexão ser estabelecida

### 1.3 Acessando a Interface de Configuração
1. **Abra** o navegador
2. **Digite** o endereço: `http://192.168.4.1:5000`
3. **Aguarde** carregar a página de configuração
4. **Verifique** se a interface aparece corretamente

---

## 🌐 PASSO 2: Configuração da Rede WiFi

### 2.1 Preenchendo o Formulário
1. **Nome do Dispositivo**: Digite um nome único (ex: "Sensor-Sala-01")
2. **Rede WiFi**: Selecione sua rede WiFi na lista
3. **Senha WiFi**: Digite a senha da rede selecionada
4. **Clique** em "Conectar e Registrar"

### 2.2 Processo de Conexão
1. **Aguarde** a mensagem "Conectando..."
2. **Observe** os LEDs:
   - LED principal piscando lento = Tentando conectar ao WiFi
   - LED principal aceso fixo = WiFi conectado com sucesso
3. **Aguarde** o redirecionamento automático

### 2.3 Verificação da Conexão
- Se bem-sucedido: Página de sucesso será exibida
- Se falhou: Voltará ao formulário com mensagem de erro
- **LED MQTT**: Piscará conforme o resultado do registro

---

## 📡 PASSO 3: Registro no Backend

### 3.1 Processo Automático
O ESP32 tentará automaticamente se registrar no backend:

1. **Conexão HTTP** para `http://IP_BACKEND:8000/api/devices/pending`
2. **Envio de dados**:
   ```json
   {
     "mac_address": "3C:84:27:C8:49:F0",
     "device_name": "Nome-Digitado",
     "ip_address": "192.168.0.X",
     "wifi_ssid": "Nome-da-Rede",
     "registered_at": timestamp,
     "status": "pending"
   }
   ```

### 3.2 Feedback via LED MQTT
- **5 piscadas rápidas**: Registro bem-sucedido (novo dispositivo)
- **3 piscadas médias**: Dispositivo já registrado e ativado
- **2 piscadas lentas**: Dispositivo já registrado, aguardando ativação
- **10 piscadas muito rápidas**: Erro de conexão/rede

---

## 💻 PASSO 4: Gerenciamento via Interface Web

### 4.1 Acessando a Interface de Administração
1. **Abra** o navegador
2. **Digite**: `http://IP_SERVIDOR:8001/pending-devices`
3. **Visualize** a lista de dispositivos pendentes

### 4.2 Ativando o Dispositivo
1. **Localize** seu dispositivo na lista
2. **Clique** no botão "Ativar"
3. **Preencha** as informações de ativação:
   - **Tópico MQTT**: Nome do tópico para mensagens
   - **Localização**: Onde o dispositivo está instalado
   - **Observações**: Informações adicionais (opcional)
4. **Clique** em "Ativar Dispositivo"

### 4.3 Gerenciamento de Dispositivos
- **Visualizar**: Ver detalhes do dispositivo
- **Ativar**: Confirmar e configurar o dispositivo
- **Rejeitar**: Recusar o registro do dispositivo
- **Excluir**: Remover dispositivo da lista

---

## 📊 PASSO 5: Verificação no Backend API

### 5.1 Testando Endpoints via curl

#### Listar Dispositivos Pendentes
```bash
curl -X GET "http://IP_SERVIDOR:8000/api/devices/pending"
```

#### Verificar Dispositivo Específico
```bash
curl -X GET "http://IP_SERVIDOR:8000/api/devices/pending/{ID}"
```

#### Ativar Dispositivo
```bash
curl -X POST "http://IP_SERVIDOR:8000/api/devices/pending/{ID}/activate" \
  -H "Content-Type: application/json" \
  -d '{
    "topic": "iot/sensor01",
    "location": "Sala Principal",
    "notes": "Sensor de temperatura"
  }'
```

---

## 🔔 PASSO 6: Teste de Mensagens MQTT

### 6.1 Configuração do Cliente MQTT
1. **Configure** um cliente MQTT (ex: MQTT Explorer, mosquitto_pub)
2. **Conecte** ao broker MQTT configurado
3. **Use** as credenciais configuradas no sistema

### 6.2 Enviando Mensagem para o Dispositivo
```bash
# Exemplo usando mosquitto_pub
mosquitto_pub -h IP_BROKER -t "iot/sensor01/comando" -m "ligar_led"
```

### 6.3 Verificando Recebimento
1. **Observe** o comportamento do ESP32
2. **Verifique** logs no Serial Monitor (se conectado)
3. **Confirme** ações executadas pelo dispositivo

### 6.4 Monitorando Mensagens do Dispositivo
```bash
# Escutar mensagens do dispositivo
mosquitto_sub -h IP_BROKER -t "iot/sensor01/status"
```

---

## 🚨 Solução de Problemas

### Problemas de Conexão WiFi
- **LED piscando rápido contínuo**: Não consegue conectar ao WiFi
  - Verifique senha da rede
  - Confirme se a rede está disponível
  - Reinicie o ESP32 e tente novamente

### Problemas de Registro
- **LED MQTT com 10 piscadas rápidas**: Erro de rede
  - Verifique se o backend está rodando
  - Confirme se ESP32 e servidor estão na mesma rede
  - Teste conectividade com ping

### Problemas na Interface Web
- **404 Not Found**: Verifique se o servidor Laravel está rodando
- **Erro de conexão**: Confirme o IP e porta do servidor
- **Dados não aparecem**: Verifique se o backend API está funcionando

---

## 📞 Status dos LEDs - Referência Rápida

### LED Principal (Conexão)
- **Desligado**: Dispositivo desligado
- **Piscando rápido**: Modo AP ativo / Erro de conexão WiFi
- **Piscando lento**: Tentando conectar ao WiFi
- **Aceso fixo**: WiFi conectado com sucesso

### LED MQTT (Notificações)
- **Desligado**: Estado normal
- **5 piscadas rápidas**: Registro bem-sucedido
- **3 piscadas médias**: Dispositivo já ativado
- **2 piscadas lentas**: Aguardando ativação
- **10 piscadas muito rápidas**: Erro de conexão

---

## 🔄 Fluxo Completo Resumido

1. **Liga ESP32** → LED principal piscando rápido
2. **Conecta ao AP** `IOT-Zontec` → Acessa `192.168.4.1:5000`
3. **Configura WiFi** → LED principal piscando lento
4. **WiFi conectado** → LED principal aceso fixo
5. **Registro automático** → LED MQTT pisca conforme resultado
6. **Acessa interface web** → `IP_SERVIDOR:8001/pending-devices`
7. **Ativa dispositivo** → Configura tópico MQTT
8. **Testa MQTT** → Envia/recebe mensagens

---

## 📋 Checklist de Verificação

- [ ] ESP32 ligado e LEDs funcionando
- [ ] Rede `IOT-Zontec` visível
- [ ] Interface de configuração carregando
- [ ] Credenciais WiFi corretas
- [ ] Backend API rodando na porta 8000
- [ ] Interface web acessível na porta 8001
- [ ] Dispositivo aparecendo na lista pendente
- [ ] Ativação realizada com sucesso
- [ ] Tópico MQTT configurado
- [ ] Mensagens MQTT funcionando

---

## 🎯 Sistema Totalmente Funcional! 

Após seguir todos os passos, seu dispositivo IoT estará:
- ✅ Conectado à rede WiFi
- ✅ Registrado no backend
- ✅ Ativado e configurado
- ✅ Pronto para comunicação MQTT
- ✅ Monitorável via interface web

**🚀 O sistema está pronto para uso em produção!** 