#!/bin/bash

# Script de Deploy da API MQTT Laravel
# Servidor de destino: 10.100.0.200
# Servidor Mosquitto: 10.100.0.21

set -e

echo "🚀 Iniciando deploy da API MQTT Laravel..."

# Configurações
SERVIDOR_DESTINO="10.100.0.200"
USUARIO_DESTINO="roboflex"
SENHA_DESTINO="Roboflex()123"
PASTA_DESTINO="/root/api-mqtt"

SERVIDOR_MQTT="10.100.0.21"
USUARIO_MQTT="sysadmin"
SENHA_MQTT="yhvh77"

echo "📋 Configurações:"
echo "  - Servidor destino: $SERVIDOR_DESTINO"
echo "  - Usuário: $USUARIO_DESTINO"
echo "  - Pasta: $PASTA_DESTINO"
echo "  - Servidor MQTT: $SERVIDOR_MQTT"

# Função para executar comandos remotos
executar_remoto() {
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "$1"
}

# Função para executar comandos no servidor MQTT
executar_mqtt() {
    sshpass -p "$SENHA_MQTT" ssh -o StrictHostKeyChecking=no "$USUARIO_MQTT@$SERVIDOR_MQTT" "$1"
}

echo "🔧 Verificando conectividade com o servidor..."
if ! executar_remoto "echo 'Conexão OK'"; then
    echo "❌ Erro: Não foi possível conectar ao servidor $SERVIDOR_DESTINO"
    exit 1
fi

echo "🔧 Verificando conectividade com o servidor MQTT..."
if ! executar_mqtt "echo 'Conexão MQTT OK'"; then
    echo "❌ Erro: Não foi possível conectar ao servidor MQTT $SERVIDOR_MQTT"
    exit 1
fi

echo "📦 Preparando arquivos para upload..."

# Criar arquivo .env para produção
cat > .env.production << EOF
APP_NAME="API MQTT"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://10.100.0.200:8000

DB_CONNECTION=sqlite
DB_DATABASE=/root/api-mqtt/database/database.sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

# MQTT Configuration
MQTT_HOST=10.100.0.21
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_CLIENT_ID=laravel_mqtt_client_production
EOF

echo "🗂️ Criando estrutura de pastas no servidor..."
executar_remoto "mkdir -p $PASTA_DESTINO"
executar_remoto "mkdir -p $PASTA_DESTINO/database"
executar_remoto "mkdir -p $PASTA_DESTINO/storage/logs"
executar_remoto "mkdir -p $PASTA_DESTINO/storage/framework/cache"
executar_remoto "mkdir -p $PASTA_DESTINO/storage/framework/sessions"
executar_remoto "mkdir -p $PASTA_DESTINO/storage/framework/views"

echo "📤 Fazendo upload dos arquivos..."
# Upload dos arquivos principais
sshpass -p "$SENHA_DESTINO" scp -o StrictHostKeyChecking=no -r \
    app/ \
    bootstrap/ \
    config/ \
    database/ \
    public/ \
    resources/ \
    routes/ \
    storage/ \
    vendor/ \
    artisan \
    composer.json \
    composer.lock \
    .env.production \
    "$USUARIO_DESTINO@$SERVIDOR_DESTINO:$PASTA_DESTINO/"

echo "🔧 Configurando ambiente no servidor..."
executar_remoto "cd $PASTA_DESTINO && mv .env.production .env"

echo "📦 Instalando dependências PHP..."
executar_remoto "cd $PASTA_DESTINO && composer install --no-dev --optimize-autoloader"

echo "🔑 Gerando chave da aplicação..."
executar_remoto "cd $PASTA_DESTINO && php artisan key:generate"

echo "🗄️ Configurando banco de dados..."
executar_remoto "cd $PASTA_DESTINO && touch database/database.sqlite"
executar_remoto "cd $PASTA_DESTINO && php artisan migrate --force"

echo "🔧 Configurando permissões..."
executar_remoto "cd $PASTA_DESTINO && chmod -R 755 storage"
executar_remoto "cd $PASTA_DESTINO && chmod -R 755 bootstrap/cache"

echo "🔧 Verificando status do Mosquitto no servidor MQTT..."
executar_mqtt "sudo systemctl status mosquitto || sudo systemctl start mosquitto"

echo "🚀 Criando script de inicialização..."
cat > start_api.sh << 'EOF'
#!/bin/bash
cd /root/api-mqtt
php artisan serve --host=0.0.0.0 --port=8000
EOF

sshpass -p "$SENHA_DESTINO" scp -o StrictHostKeyChecking=no start_api.sh "$USUARIO_DESTINO@$SERVIDOR_DESTINO:$PASTA_DESTINO/"
executar_remoto "cd $PASTA_DESTINO && chmod +x start_api.sh"

echo "🔧 Criando serviço systemd..."
cat > api-mqtt.service << EOF
[Unit]
Description=API MQTT Laravel
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=$PASTA_DESTINO
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

sshpass -p "$SENHA_DESTINO" scp -o StrictHostKeyChecking=no api-mqtt.service "$USUARIO_DESTINO@$SERVIDOR_DESTINO:/tmp/"
executar_remoto "sudo mv /tmp/api-mqtt.service /etc/systemd/system/"
executar_remoto "sudo systemctl daemon-reload"
executar_remoto "sudo systemctl enable api-mqtt"
executar_remoto "sudo systemctl start api-mqtt"

echo "🧪 Testando a API..."
sleep 5

# Teste básico da API
if curl -s http://10.100.0.200:8000/api/mqtt/topics > /dev/null; then
    echo "✅ API está funcionando!"
else
    echo "⚠️ API pode não estar respondendo ainda. Aguarde alguns segundos."
fi

echo "📋 Criando script de teste..."
cat > teste_api_deploy.sh << EOF
#!/bin/bash
echo "🧪 Testando endpoints da API..."

echo "1. Testando listagem de tópicos:"
curl -X GET http://10.100.0.200:8000/api/mqtt/topics

echo -e "\n\n2. Testando criação de tópico:"
curl -X POST http://10.100.0.200:8000/api/mqtt/topics \\
  -H "Content-Type: application/json" \\
  -d '{"name": "teste/deploy", "description": "Teste após deploy"}'

echo -e "\n\n3. Testando envio de mensagem:"
curl -X POST http://10.100.0.200:8000/api/mqtt/send-message \\
  -H "Content-Type: application/json" \\
  -d '{"topico": "teste/deploy", "mensagem": "deploy_sucesso"}'

echo -e "\n\n✅ Testes concluídos!"
EOF

chmod +x teste_api_deploy.sh

echo "🎉 Deploy concluído com sucesso!"
echo ""
echo "📋 Resumo do deploy:"
echo "  ✅ API instalada em: $PASTA_DESTINO"
echo "  ✅ Serviço systemd criado: api-mqtt"
echo "  ✅ API rodando em: http://10.100.0.200:8000"
echo "  ✅ Conectado ao Mosquitto em: $SERVIDOR_MQTT"
echo ""
echo "🔧 Comandos úteis:"
echo "  - Verificar status: sudo systemctl status api-mqtt"
echo "  - Reiniciar API: sudo systemctl restart api-mqtt"
echo "  - Ver logs: sudo journalctl -u api-mqtt -f"
echo "  - Testar API: ./teste_api_deploy.sh"
echo ""
echo "📡 Endpoints disponíveis:"
echo "  - GET  http://10.100.0.200:8000/api/mqtt/topics"
echo "  - POST http://10.100.0.200:8000/api/mqtt/topics"
echo "  - POST http://10.100.0.200:8000/api/mqtt/send-message" 