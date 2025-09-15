# 🌱 Sistema de Seeding Completo - MQTT IoT

## 📋 Visão Geral

Este sistema de seeding popula completamente o banco de dados do sistema MQTT IoT com dados realistas e organizados hierarquicamente. Inclui empresas, departamentos, tipos de dispositivos, usuários e tópicos MQTT.

## 🚀 Como Usar

### Comando Rápido (Recomendado)
```bash
# Resetar tudo e popular com dados frescos
php artisan system:seed --fresh

# Apenas popular (sem resetar)
php artisan system:seed
```

### Comandos Tradicionais
```bash
# Resetar e popular
php artisan migrate:fresh --seed

# Apenas popular
php artisan db:seed
```

## 📊 Estrutura dos Dados Criados

### 🏢 **5 Empresas**
- TechCorp Indústria
- Manufatura Avançada Ltda
- AutoParts Brasil
- Smart Factory Solutions
- Indústria 4.0 Inovações

### 🏗️ **20 Departamentos** (Estrutura Hierárquica)

#### **TechCorp Indústria:**
```
🏢 TechCorp Indústria
├── 📋 Produção (Nível 1)
│   ├── 📁 Linha 1 (Nível 2)
│   │   ├── 📄 Setor A (Nível 3)
│   │   └── 📄 Setor B (Nível 3)
│   ├── 📁 Linha 2 (Nível 2)
│   └── 📁 Montagem (Nível 2)
│       ├── 📄 Estação 1 (Nível 3)
│       └── 📄 Estação 2 (Nível 3)
├── 📋 Manutenção (Nível 1)
│   ├── 📁 Manutenção Preventiva (Nível 2)
│   └── 📁 Manutenção Corretiva (Nível 2)
└── 📋 Qualidade (Nível 1)
    ├── 📁 Controle de Qualidade (Nível 2)
    └── 📁 Laboratório (Nível 2)
```

#### **Manufatura Avançada Ltda:**
```
🏢 Manufatura Avançada Ltda
├── 📋 Fabricação (Nível 1)
│   ├── 📁 Usinagem (Nível 2)
│   └── 📁 Soldagem (Nível 2)
└── 📋 Logística (Nível 1)
    ├── 📁 Expedição (Nível 2)
    └── 📁 Recebimento (Nível 2)
```

### 📱 **10 Tipos de Dispositivos IoT** (com Especificações Completas)

1. **🌡️ Sensor de Temperatura**
   - Voltagem: 3.3V
   - Protocolo: WiFi
   - Range: -40°C a +125°C
   - Precisão: ±0.5°C

2. **💧 Sensor de Umidade**
   - Voltagem: 3.3V - 5V
   - Range: 0% a 100% RH
   - Precisão: ±2% RH

3. **💡 LED de Controle**
   - Voltagem: 12V/24V
   - Cores: RGB
   - Controle: PWM

4. **🚶 Sensor de Movimento**
   - Alcance: 7 metros
   - Ângulo: 120°
   - Protocolo: WiFi

5. **⚡ Relé de Controle**
   - Voltagem Controle: 3.3V
   - Voltagem Carga: 250V AC / 30V DC
   - Corrente Máx: 10A

6. **🔧 Sensor de Pressão**
   - Range: 0-100 PSI
   - Precisão: ±0.25%
   - Material: Aço inoxidável

7. **📹 Câmera de Monitoramento**
   - Resolução: 1080p Full HD
   - Visão Noturna: IR até 20m
   - Protocolo: WiFi/Ethernet

8. **📳 Sensor de Vibração**
   - Range Frequência: 0.5Hz - 1kHz
   - Sensibilidade: 100mV/g
   - Protocolo: WiFi/LoRa

9. **📺 Display OLED**
   - Tamanho: 0.96 polegadas
   - Resolução: 128x64 pixels
   - Interface: I2C/SPI

10. **🌬️ Sensor de Qualidade do Ar**
    - Parâmetros: CO2, VOCs, PM2.5, PM10
    - Range CO2: 400-10000 ppm
    - Precisão CO2: ±50ppm

### 👥 **11 Usuários** (Diferentes Níveis)

#### **Administradores:**
- **admin@sistema.com** / admin123 (Admin Geral)
- **supervisor@empresa.com** / supervisor123 (Supervisor Geral)

#### **Usuários Comuns:**
- **carlos.silva@techcorp.com** / gerente123 (Gerente)
- **ana.santos@techcorp.com** / supervisor123 (Supervisor)
- **pedro.oliveira@techcorp.com** / tecnico123 (Técnico)
- **maria.costa@techcorp.com** / operador123 (Operador)
- **joao.ferreira@techcorp.com** / operador123 (Operador)
- **roberto.lima@techcorp.com** / tecnico123 (Técnico)
- **fernanda.rodrigues@manufatura.com** / gerente123 (Gerente)
- **lucas.almeida@manufatura.com** / supervisor123 (Supervisor)
- **carla.mendes@manufatura.com** / tecnico123 (Técnico)

### 📡 **91 Tópicos MQTT** (Estrutura Realista)

#### **Padrão de Nomenclatura:**
```
iot/{departamento}/{tipo_dispositivo}/{mac_address}
```

#### **Exemplos:**
```
iot/producao/sensor_de_temperatura/8EC37C5591EB
iot/manutencao/led_de_controle/AABBCCDDEEFF
iot/qualidade/camera_de_monitoramento/123456789ABC
```

#### **Tópicos de Sistema:**
```
system/heartbeat      - Monitoramento de conectividade
system/alerts         - Alertas e notificações
system/status         - Status geral do sistema
system/config         - Configurações do sistema
```

#### **Tópicos de Broadcast:**
```
broadcast/maintenance  - Comandos de manutenção
broadcast/emergency    - Comandos de emergência
broadcast/shift_change - Mudança de turno
```

#### **Tópicos Específicos:**
```
sensors/temperature/zone_a/average - Média temperatura Zona A
sensors/temperature/zone_b/average - Média temperatura Zona B
actuators/leds/production_line/status - Status LEDs produção
actuators/leds/emergency/control - Controle LEDs emergência
```

## 🔧 Seeders Individuais

### Execução Separada
```bash
# Empresas
php artisan db:seed --class=CompanySeeder

# Departamentos
php artisan db:seed --class=DepartmentSeeder

# Tipos de Dispositivos
php artisan db:seed --class=DeviceTypeSeeder

# Usuários
php artisan db:seed --class=UserSeeder

# Tópicos MQTT
php artisan db:seed --class=TopicSeeder
```

## ⚙️ Personalização

### Modificar Dados
Edite os arquivos em `database/seeders/`:
- `CompanySeeder.php` - Empresas
- `DepartmentSeeder.php` - Departamentos e hierarquia
- `DeviceTypeSeeder.php` - Tipos de dispositivos e especificações
- `UserSeeder.php` - Usuários e credenciais
- `TopicSeeder.php` - Tópicos MQTT

### Adicionar Novos Seeders
```bash
php artisan make:seeder NovoSeeder
```

## 🌐 Integração com Aplicações

### URLs de Acesso:
- **📊 Dashboard Web:** http://10.102.0.101:8001
- **📱 App Config:** http://10.102.0.101:8002
- **🔧 API Backend:** http://10.102.0.101:8000/api

### Login Principal:
- **Email:** admin@sistema.com
- **Senha:** admin123

## 🛠️ Dependências

### Ordem de Execução:
1. **Companies** (Base para departamentos e usuários)
2. **Departments** (Depende de Companies)
3. **DeviceTypes** (Independente)
4. **Users** (Depende de Companies)
5. **Topics** (Depende de Departments e DeviceTypes)

### Relacionamentos:
- Departamentos → Empresas
- Usuários → Empresas
- Tópicos → Departamentos + Tipos de Dispositivos

## 📈 Estatísticas Finais

Após execução completa:
- ✅ **5 Empresas** criadas
- ✅ **20 Departamentos** organizados hierarquicamente
- ✅ **10 Tipos de Dispositivos** com especificações completas
- ✅ **11 Usuários** com diferentes níveis de acesso
- ✅ **91 Tópicos MQTT** seguindo padrões industriais

## 🎯 Próximos Passos

1. **Acesse o Dashboard Web** para gerenciar o sistema
2. **Use o App Config** para registrar novos dispositivos
3. **Monitore os Tópicos MQTT** em tempo real
4. **Configure usuários** e permissões conforme necessário
5. **Customize os dados** editando os seeders conforme sua necessidade

---

**💡 Dica:** Use `php artisan system:seed --fresh` sempre que quiser resetar completamente o sistema com dados limpos! 