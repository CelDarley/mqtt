# Configuração do Servidor API MQTT

## 🖥️ Informações do Servidor

- **IP**: 10.102.0.21
- **Porta**: 8000
- **Usuário**: darley
- **Senha**: yhvh77
- **Sistema**: Ubuntu Linux
- **Framework**: Laravel 12.x

## 🚀 Como Iniciar o Servidor

### Método 1: Script Automático
```bash
./start_server.sh
```

### Método 2: Comando Manual
```bash
php artisan serve --host=10.102.0.21 --port=8000
```

## 🔧 Configurações de Rede

### IP Configurado
```bash
# Verificar IP configurado
ip addr show eno1

# Configurar IP (se necessário)
sudo ip addr add 10.102.0.21/24 dev eno1
```

### Serviços Necessários
```bash
# Verificar status do Mosquitto
sudo systemctl status mosquitto

# Iniciar Mosquitto (se necessário)
sudo systemctl start mosquitto

# Verificar status do MySQL
sudo systemctl status mysql

# Iniciar MySQL (se necessário)
sudo systemctl start mysql
```

## 🧪 Testes

### Teste Automático
```bash
./teste_api_ip.sh
```

### Teste Manual
```bash
# Listar tópicos
curl -X GET http://10.102.0.21:8000/api/mqtt/topics

# Criar tópico
curl -X POST http://10.102.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "teste/ip", "description": "Teste no IP"}'

# Enviar mensagem
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "teste/ip", "mensagem": "liberar"}'
```

## 📋 Endpoints Disponíveis

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `http://10.102.0.21:8000/api/mqtt/topics` | Criar novo tópico |
| GET | `http://10.102.0.21:8000/api/mqtt/topics` | Listar todos os tópicos |
| GET | `http://10.102.0.21:8000/api/mqtt/topics/{id}` | Mostrar tópico específico |
| PATCH | `http://10.102.0.21:8000/api/mqtt/topics/{id}/deactivate` | Desativar tópico |
| POST | `http://10.102.0.21:8000/api/mqtt/send-message` | Enviar mensagem para tópico |

## 🔍 Monitoramento

### Logs do Laravel
```bash
tail -f storage/logs/laravel.log
```

### Status dos Serviços
```bash
# Status do Mosquitto
sudo systemctl status mosquitto

# Status do MySQL
sudo systemctl status mysql

# Processos PHP
ps aux | grep "php artisan serve"
```

### Conexões de Rede
```bash
# Verificar porta 8000
netstat -tlnp | grep 8000

# Verificar conexões MQTT
netstat -tlnp | grep 1883
```

## 🛠️ Troubleshooting

### Problema: Servidor não inicia
```bash
# Verificar se o IP está configurado
ip addr show eno1 | grep 10.102.0.21

# Verificar se a porta está livre
netstat -tlnp | grep 8000

# Verificar logs do Laravel
tail -f storage/logs/laravel.log
```

### Problema: Erro de conexão MQTT
```bash
# Verificar status do Mosquitto
sudo systemctl status mosquitto

# Reiniciar Mosquitto
sudo systemctl restart mosquitto

# Verificar logs do Mosquitto
sudo journalctl -u mosquitto -f
```

### Problema: Erro de banco de dados
```bash
# Verificar status do MySQL
sudo systemctl status mysql

# Testar conexão
mysql -u roboflex -p'Roboflex()123' -e "USE mqtt; SHOW TABLES;"
```

## 📊 Status Atual

- ✅ **IP 10.102.0.21**: Configurado
- ✅ **Servidor Laravel**: Rodando na porta 8000
- ✅ **Mosquitto**: Ativo
- ✅ **MySQL**: Ativo
- ✅ **API**: Funcionando
- ✅ **Testes**: Passando

## 🔐 Segurança

- **Firewall**: Verificar se a porta 8000 está liberada
- **Acesso**: Apenas usuário darley tem acesso
- **Logs**: Monitoramento de todas as operações
- **Validação**: Todos os inputs são validados

## 📞 Suporte

Para problemas ou dúvidas:
1. Verificar logs: `tail -f storage/logs/laravel.log`
2. Testar conectividade: `ping 10.102.0.21`
3. Verificar serviços: `sudo systemctl status mosquitto mysql`
4. Executar testes: `./teste_api_ip.sh` 