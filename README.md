# 🚀 Sistema MQTT IoT Completo

Sistema completo de gerenciamento IoT com interface web, API REST e atualizações OTA para dispositivos ESP32.

## 📋 Funcionalidades Principais

### 🏢 **Gerenciamento Empresarial**
- ✅ CRUD completo de Empresas
- ✅ CRUD de Departamentos com hierarquia organizacional  
- ✅ Sistema de validação e dependências
- ✅ Interface moderna e responsiva

### 📱 **Gerenciamento de Dispositivos**
- ✅ CRUD de Tipos de Dispositivos IoT
- ✅ Especificações técnicas em JSON
- ✅ Status ativo/inativo
- ✅ Integração com sistema OTA

### 🔄 **Sistema OTA (Over-The-Air)**
- ✅ Servidor nginx para distribuição de firmware
- ✅ Versionamento automático de firmware
- ✅ API completa para gerenciamento de updates
- ✅ Scripts automatizados para deploy
- ✅ Logs detalhados de atualizações
- ✅ Suporte a múltiplos tipos de dispositivos

### 👥 **Sistema de Usuários**
- ✅ Interface de gerenciamento
- ✅ Diferentes perfis (Admin, Operador, Visualizador)
- ✅ Sistema de autenticação

### 🌐 **Interface Web**
- ✅ Dashboard responsivo
- ✅ Formulários interativos com validação
- ✅ Modais informativos
- ✅ Design moderno com gradientes
- ✅ Filtros e busca em tempo real

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 12 (PHP 8.3)
- **Frontend**: Blade Templates + JavaScript + CSS
- **Database**: MySQL/SQLite
- **Servidor Web**: nginx 
- **Dispositivos**: ESP32/Arduino
- **Comunicação**: MQTT + HTTP
- **Controle de Versão**: Git

## 📁 Estrutura do Projeto

```
mqtt/
├── iot-config-app-laravel/     # App móvel/dispositivos
├── iot-config-web-laravel/     # Interface web administrativa
├── mqtt/                       # API backend principal
├── firmware-final/             # Código ESP32/Arduino
├── documentacao/               # Guias e manuais
└── scripts/                    # Automação e deploy
```

## 🚀 Instalação e Configuração

### **Pré-requisitos**
- PHP 8.3+
- Composer
- Node.js + npm
- MySQL/SQLite
- nginx
- Git

### **1. Clone o Repositório**
```bash
git clone https://github.com/SEU_USUARIO/mqtt-iot-system.git
cd mqtt-iot-system
```

### **2. Configure o Backend**
```bash
cd mqtt/
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve --host=0.0.0.0 --port=8000
```

### **3. Configure o Frontend Web**
```bash
cd iot-config-web-laravel/
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --host=0.0.0.0 --port=8002
```

### **4. Configure o App de Dispositivos**
```bash
cd iot-config-app-laravel/
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --host=0.0.0.0 --port=8001
```

### **5. Configure Sistema OTA**
```bash
# Como root/sudo
sudo ./setup-nginx-ota.sh
sudo ./create-firmware-structure.sh
```

## 🌐 URLs de Acesso

- **🖥️ Interface Web**: http://localhost:8002
- **📱 App Dispositivos**: http://localhost:8001  
- **🔧 API Backend**: http://localhost:8000/api
- **📦 Servidor OTA**: http://firmware.iot.local

## 📋 Como Usar

### **1. Gerenciar Empresas e Departamentos**
1. Acesse http://localhost:8002
2. Navegue para "Empresas" ou "Departamentos"
3. Use os CRUDs para gerenciar organizações

### **2. Configurar Tipos de Dispositivos**
1. Vá em "Tipos de Dispositivo"
2. Cadastre novos tipos com especificações JSON
3. Configure ícones e descrições

### **3. Atualizar Firmware via OTA**
1. Adicione firmware: `./adicionar_firmware.sh /caminho/firmware.bin tipo_dispositivo v1.0.0`
2. Clique "Atualizar Firmware" na interface web
3. Monitore logs de atualização

### **4. Configurar Dispositivos ESP32**
1. Use o código em `firmware-final/esp32_ota_client/`
2. Configure WiFi e MQTT
3. Compile e grave no ESP32

## 📊 APIs Disponíveis

### **Empresas**
- `GET /api/mqtt/companies` - Listar empresas
- `POST /api/mqtt/companies` - Criar empresa
- `PUT /api/mqtt/companies/{id}` - Editar empresa
- `DELETE /api/mqtt/companies/{id}` - Deletar empresa

### **Departamentos**  
- `GET /api/mqtt/departments` - Listar departamentos
- `POST /api/mqtt/departments` - Criar departamento
- `PUT /api/mqtt/departments/{id}` - Editar departamento
- `DELETE /api/mqtt/departments/{id}` - Deletar departamento

### **Tipos de Dispositivos**
- `GET /api/mqtt/device-types` - Listar tipos
- `POST /api/mqtt/device-types` - Criar tipo
- `PUT /api/mqtt/device-types/{id}` - Editar tipo
- `DELETE /api/mqtt/device-types/{id}` - Deletar tipo

### **Sistema OTA**
- `POST /api/mqtt/device-types/{id}/ota-update` - Iniciar OTA
- `GET /api/mqtt/device-types/{id}/firmware-info` - Info firmware
- `GET /api/mqtt/ota-updates` - Listar updates
- `GET /api/mqtt/ota-updates/{id}` - Status update

## 🔧 Scripts Úteis

- `./adicionar_firmware.sh` - Adicionar firmware OTA
- `./setup-nginx-ota.sh` - Configurar servidor nginx
- `./create-firmware-structure.sh` - Criar estrutura de pastas
- `./start_servers.sh` - Iniciar todos os servidores
- `./update_all_repos.sh` - Atualizar repositórios

## 📝 Documentação Adicional

- [📋 Arquivos Essenciais](ARQUIVOS_ESSENCIAIS.md)
- [🔄 Como Adicionar Firmware OTA](COMO_COLOCAR_FIRMWARE_OTA.md)
- [🏢 CRUDs Implementados](CRUDS_IMPLEMENTADOS.md)
- [⚙️ Configurações ESP32](FUNCIONAMENTO_LEDS_ESP32.md)
- [🚀 Deploy no Raspberry](DEPLOY_MQTT_RASPBERRY.md)

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto é licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🆘 Suporte

Para dúvidas e suporte:
- Abra uma [Issue](https://github.com/SEU_USUARIO/mqtt-iot-system/issues)
- Consulte a [Documentação](docs/)
- Entre em contato: darley@gmail.com

---

**Desenvolvido com ❤️ para a comunidade IoT** 