# 🔧 Configurações dos Arquivos .env - Sistema MQTT IoT

## 📋 Visão Geral

Este documento contém as configurações atuais dos três arquivos `.env` do sistema MQTT IoT. Essas configurações são essenciais para o funcionamento correto de todas as aplicações.

## 🌐 Estrutura do Sistema

- **🔧 Backend API**: `mqtt/` - Porta 8000
- **📊 Dashboard Web**: `iot-config-web-laravel/` - Porta 8001  
- **📱 App Configuração**: `iot-config-app-laravel/` - Porta 8002

---

## 🔧 1. MQTT Backend (.env)

**Localização:** `mqtt/.env`  
**Porta:** 8000  
**Função:** API principal do sistema MQTT

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mqtt
DB_USERNAME=roboflex
DB_PASSWORD=Roboflex()123

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

JWT_SECRET=hy3ohBpuKJPf5rsdLeHot13wsLmt5lwm9iLsag7mJLO8bRK3ZAh0WQpOZsOP8Xm9

JWT_ALGO=HS256
```

### 🔑 Configurações Especiais - Backend:
- **JWT_SECRET**: Token para autenticação JWT
- **DB_DATABASE**: `mqtt` (banco principal)
- **APP_URL**: http://localhost (API base)

---

## 📊 2. Dashboard Web (.env)

**Localização:** `iot-config-web-laravel/.env`  
**Porta:** 8001  
**Função:** Interface web administrativa

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:6eesjZMULYiebCNAx3WE0YvG3LeqvOfrolD4A6RdRsE=
APP_DEBUG=true
APP_URL=http://localhost:8002

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mqtt
DB_USERNAME=roboflex
DB_PASSWORD=Roboflex()123

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# Cookie/Session Configuration
SESSION_SECURE_COOKIE=false
SANCTUM_STATEFUL_DOMAINS=localhost:8002
SESSION_SAME_SITE=lax
API_BASE_URL=http://10.102.0.101:8000/api
```

### 🔑 Configurações Especiais - Dashboard Web:
- **APP_URL**: http://localhost:8002
- **API_BASE_URL**: http://10.102.0.101:8000/api (conexão com backend)
- **SANCTUM_STATEFUL_DOMAINS**: localhost:8002

---

## 📱 3. App Configuração (.env)

**Localização:** `iot-config-app-laravel/.env`  
**Porta:** 8002  
**Função:** Aplicação de configuração de dispositivos

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:vkxb6U3DYe9kVwJTphnsSqAZHLTnSyfwBbe7VFHPS+Y=
APP_DEBUG=true
APP_URL=http://localhost:8001

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mqtt
DB_USERNAME=roboflex
DB_PASSWORD=Roboflex()123

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# Cookie/Session Configuration
SESSION_SECURE_COOKIE=false
SANCTUM_STATEFUL_DOMAINS=localhost:8001
SESSION_SAME_SITE=lax
API_BASE_URL=http://10.102.0.101:8000/api
```

### 🔑 Configurações Especiais - App Configuração:
- **APP_URL**: http://localhost:8001
- **API_BASE_URL**: http://10.102.0.101:8000/api (conexão com backend)
- **SANCTUM_STATEFUL_DOMAINS**: localhost:8001

---

## 🔍 Principais Diferenças

| Configuração | Backend | Dashboard Web | App Config |
|-------------|---------|---------------|------------|
| **Porta** | 8000 | 8002 | 8001 |
| **APP_KEY** | ❌ Vazio | ✅ Configurado | ✅ Configurado |
| **JWT_SECRET** | ✅ Configurado | ❌ Não aplicável | ❌ Não aplicável |
| **API_BASE_URL** | ❌ Não aplicável | ✅ http://10.102.0.101:8000/api | ✅ http://10.102.0.101:8000/api |
| **SANCTUM_DOMAINS** | ❌ Não aplicável | ✅ localhost:8002 | ✅ localhost:8001 |

---

## 🌐 Configurações de Rede

### URLs de Acesso:
- **🔧 Backend API**: http://10.102.0.101:8000
- **📊 Dashboard Web**: http://10.102.0.101:8001
- **📱 App Config**: http://10.102.0.101:8002

### Banco de Dados:
- **Host**: 127.0.0.1:3306
- **Database**: `mqtt`
- **Username**: `roboflex`
- **Password**: `Roboflex()123`

---

## 🔧 Configurações Necessárias

### Para o Backend (mqtt):
```bash
# Gerar APP_KEY se necessário
php artisan key:generate
```

### Para as Aplicações Web:
```bash
# Limpar cache de configuração
php artisan config:clear
php artisan view:clear
```

### Variáveis de Ambiente Críticas:
- **API_BASE_URL**: Deve apontar para o backend (http://10.102.0.101:8000/api)
- **DB_DATABASE**: Todas as aplicações usam o mesmo banco `mqtt`
- **JWT_SECRET**: Apenas no backend para autenticação

---

## 🚀 Como Usar

### 1. Copiar Configurações:
```bash
# Backend
cp mqtt/.env.example mqtt/.env

# Dashboard Web  
cp iot-config-web-laravel/.env.example iot-config-web-laravel/.env

# App Config
cp iot-config-app-laravel/.env.example iot-config-app-laravel/.env
```

### 2. Configurar Banco:
```bash
# Criar banco de dados
mysql -u roboflex -p
CREATE DATABASE mqtt;
```

### 3. Executar Migrações e Seeding:
```bash
cd mqtt
php artisan migrate:fresh --seed
# ou
php artisan system:seed --fresh
```

---

## 🔐 Segurança

### ⚠️ Importante:
- **Nunca commitar arquivos .env** para o repositório
- **Alterar senhas** em ambiente de produção
- **Configurar HTTPS** em produção
- **Validar JWT_SECRET** se usando autenticação

### 🛡️ Recomendações:
- Use senhas fortes para o banco de dados
- Configure SSL/TLS para conexões de produção
- Monitore logs de erro regularmente
- Faça backup das configurações importantes

---

**📅 Última Atualização:** Setembro 2025  
**🔄 Status:** Configurações em produção no servidor 10.102.0.101 