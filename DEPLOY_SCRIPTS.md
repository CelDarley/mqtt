# Scripts de Deploy da API MQTT Laravel

Este documento descreve todos os scripts de deploy criados durante o processo de implementação da API MQTT Laravel.

## 📋 Visão Geral

Durante o processo de deploy, foram criados vários scripts para automatizar a instalação e configuração da API MQTT Laravel em diferentes ambientes e servidores.

## 🚀 Scripts de Deploy

### Scripts para Servidor MQTT (10.100.0.21)

#### `deploy_completo_mqtt.sh`
- **Propósito**: Deploy completo no servidor MQTT
- **Execução**: Como root em `/root/`
- **Funcionalidades**:
  - Instala dependências PHP/Composer
  - Configura permissões
  - Cria arquivo .env
  - Executa migrações
  - Cria serviço systemd
  - Testa API
  - Cria scripts de gerenciamento

#### `deploy_mqtt_server.sh`
- **Propósito**: Deploy inicial no servidor MQTT
- **Pasta destino**: `/root/api-mqtt`
- **Usuário**: darley

#### `deploy_root_mqtt.sh`
- **Propósito**: Deploy executado como root
- **Bypass**: Problemas de sudo password

#### `deploy_interativo_mqtt.sh`
- **Propósito**: Deploy com comandos sudo interativos
- **Permite**: Usuário digitar senha manualmente

#### `deploy_manual_mqtt.sh`
- **Propósito**: Instruções manuais para deploy
- **Guia**: Usuário através de comandos manuais

### Scripts para Servidor 200 (10.100.0.200)

#### `deploy_servidor_200.sh`
- **Propósito**: Deploy inicial no servidor 200
- **Pasta destino**: `/root/api-mqtt`
- **Usuário**: darley
- **MQTT**: Conecta ao 10.100.0.21

#### `deploy_servidor_200_corrigido.sh`
- **Propósito**: Correção de permissões do composer.json
- **Problema resolvido**: `Permission denied` no composer.json

#### `deploy_servidor_200_final.sh`
- **Propósito**: Deploy final com correções
- **Melhorias**: Permissões específicas para Composer

#### `deploy_servidor_200_final_corrigido.sh`
- **Propósito**: Correção do vendor/autoload.php
- **Problema resolvido**: `Permission denied` no autoload.php

#### `deploy_servidor_200_solucao_definitiva.sh`
- **Propósito**: Solução definitiva com testes
- **Inclui**: Testes de diagnóstico PHP
- **Debug**: Informações detalhadas sobre permissões

#### `deploy_servidor_200_solucao_final.sh`
- **Propósito**: Solução final com execução como root
- **Estratégia**: Executa comandos Laravel como root
- **Ambiente**: Configura variáveis de ambiente

#### `deploy_servidor_200_ultima_tentativa.sh`
- **Propósito**: Última tentativa com correções agressivas
- **Inclui**: Verificação SELinux
- **Métodos**: Múltiplas estratégias de fallback

### Scripts de Upload

#### `upload_temp_mqtt.sh`
- **Propósito**: Upload para pasta temporária
- **Destino**: `/home/darley/temp-api-mqtt/`
- **Estratégia**: Evita problemas de permissão com `/root/`

#### `upload_servidor_200.sh`
- **Propósito**: Upload para servidor 200
- **Destino**: `/home/darley/temp-api-mqtt/`

#### `upload_servidor_200_corrigido.sh`
- **Propósito**: Upload corrigido para servidor 200
- **Melhorias**: Tratamento de erros

#### `upload_arquivos_mqtt.sh`
- **Propósito**: Upload para servidor MQTT
- **Destino**: `/home/darley/temp-api-mqtt/`

### Scripts de Instalação de Requisitos

#### `instalar_requisitos_200.sh`
- **Propósito**: Instalar PHP e Composer no servidor 200
- **Problema**: `E: Não foi possível encontrar o pacote php8.2`

#### `instalar_php_200.sh`
- **Propósito**: Adicionar PPA e instalar PHP 8.2
- **Inclui**: `ppa:ondrej/php`

#### `instalar_php_corrigido_200.sh`
- **Propósito**: Correção da instalação PHP
- **Problema resolvido**: `php8.2-json` é pacote virtual
- **Estratégia**: Instala `php8.2-cli` primeiro

### Scripts de Deploy Via Intermediário

#### `deploy_to_server.sh`
- **Propósito**: Deploy direto para 10.100.0.200
- **Problema**: `ssh: connect to host 10.100.0.200 port 22: Connection refused`

#### `deploy_via_mqtt_server.sh`
- **Propósito**: Usar 10.100.0.21 como intermediário
- **Estratégia**: Deploy via servidor MQTT
- **Problema**: `ping: socket: Operation not permitted`

## 🔧 Problemas Resolvidos

### 1. Conectividade SSH
- **Problema**: `ssh: connect to host 10.100.0.200 port 22: Connection refused`
- **Solução**: Deploy direto no servidor com scripts manuais

### 2. Permissões sudo
- **Problema**: `sudo: a terminal is required to read the password`
- **Solução**: Scripts executados como root diretamente

### 3. Permissões scp
- **Problema**: `scp: /root/api-mqtt/: Permission denied`
- **Solução**: Upload para pasta temporária (`/home/darley/temp-api-mqtt/`)

### 4. Instalação PHP
- **Problema**: `E: Não foi possível encontrar o pacote php8.2`
- **Solução**: Adicionar PPA `ppa:ondrej/php`

### 5. Pacote virtual PHP
- **Problema**: `E: O pacote 'php8.2-json' não tem candidato para instalação`
- **Solução**: Instalar `php8.2-cli` primeiro (fornece json)

### 6. Permissões composer.json
- **Problema**: `file_get_contents(./composer.json): Failed to open stream: Permission denied`
- **Solução**: Configurar permissões específicas antes do composer install

### 7. Permissões autoload.php
- **Problema**: `require(/root/api-mqtt/vendor/autoload.php): Failed to open stream: Permission denied`
- **Solução**: Executar comandos Laravel como root com ambiente configurado

## 📊 Estratégias de Deploy

### Estratégia 1: Deploy Direto
- **Scripts**: `deploy_to_server.sh`
- **Problema**: Conectividade SSH

### Estratégia 2: Deploy Via Intermediário
- **Scripts**: `deploy_via_mqtt_server.sh`
- **Problema**: Permissões de rede

### Estratégia 3: Deploy Manual
- **Scripts**: `deploy_manual_mqtt.sh`
- **Vantagem**: Controle total pelo usuário

### Estratégia 4: Deploy Como Root
- **Scripts**: `deploy_root_mqtt.sh`, `deploy_servidor_200_solucao_final.sh`
- **Vantagem**: Evita problemas de permissão

## 🎯 Scripts Recomendados

### Para Servidor MQTT (10.100.0.21)
```bash
# Copiar script para /root
sudo cp /home/darley/deploy_completo_mqtt.sh /root/

# Executar como root
cd /root
sudo ./deploy_completo_mqtt.sh
```

### Para Servidor 200 (10.100.0.200)
```bash
# Copiar script para /root
sudo cp /home/darley/deploy_servidor_200_solucao_final.sh /root/

# Executar como root
cd /root
sudo ./deploy_servidor_200_solucao_final.sh
```

## 📋 Comandos Pós-Deploy

### Verificar Status
```bash
systemctl status api-mqtt
```

### Testar API
```bash
curl http://10.100.0.200:8000/api/mqtt/topics
```

### Ver Logs
```bash
journalctl -u api-mqtt -f
```

### Gerenciar API
```bash
cd /root
./gerenciar_api_200.sh
```

## 🔍 Lições Aprendidas

1. **Permissões são críticas**: Sempre configurar permissões antes de executar Composer
2. **Contexto de execução**: PHP executado como usuário diferente pode ter problemas de acesso
3. **Variáveis de ambiente**: Configurar APP_ENV e APP_DEBUG é essencial
4. **Fallback strategies**: Ter múltiplas estratégias de deploy
5. **Debug detalhado**: Testes específicos ajudam a identificar problemas
6. **Serviços systemd**: Mais confiáveis que scripts manuais

## 📝 Notas Importantes

- Todos os scripts devem ser executados como root
- Sempre fazer backup antes de executar scripts
- Testar conectividade antes de tentar deploy remoto
- Verificar requisitos do sistema antes do deploy
- Configurar firewall se necessário
- Documentar problemas e soluções encontradas 