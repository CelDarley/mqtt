#!/bin/bash

# Script de Deploy da API MQTT Laravel no servidor MQTT (Manual)
# Servidor MQTT: 10.100.0.21
# Usuário: darley
# Pasta: /root

set -e

echo "🚀 Iniciando deploy da API MQTT Laravel no servidor MQTT..."

# Configurações
SERVIDOR_MQTT="10.100.0.21"
USUARIO_MQTT="darley"
SENHA_MQTT="yhvh77"
PASTA_DESTINO="/root/api-mqtt"

echo "📋 Configurações:"
echo "  - Servidor MQTT: $SERVIDOR_MQTT"
echo "  - Usuário: $USUARIO_MQTT"
echo "  - Pasta destino: $PASTA_DESTINO"

# Função para executar comandos no servidor MQTT
executar_mqtt() {
    sshpass -p "$SENHA_MQTT" ssh -o StrictHostKeyChecking=no "$USUARIO_MQTT@$SERVIDOR_MQTT" "$1"
}

echo "🔧 Verificando conectividade com o servidor MQTT..."
if ! executar_mqtt "echo 'Conexão MQTT OK'"; then
    echo "❌ Erro: Não foi possível conectar ao servidor MQTT $SERVIDOR_MQTT"
    exit 1
fi

echo "✅ Conexão com servidor MQTT estabelecida!"

echo "🔧 Verificando requisitos no servidor MQTT..."
executar_mqtt "which php || echo 'PHP não encontrado'"
executar_mqtt "which composer || echo 'Composer não encontrado'"
executar_mqtt "which git || echo 'Git não encontrado'"

echo "🗂️ Criando estrutura de pastas no servidor MQTT..."
echo "⚠️ Executando comandos sudo manualmente..."

# Executar comandos sudo manualmente
echo "📝 Execute os seguintes comandos no servidor MQTT:"
echo ""
echo "1. Conecte ao servidor:"
echo "   ssh darley@10.100.0.21"
echo ""
echo "2. Execute os comandos sudo:"
echo "   sudo mkdir -p $PASTA_DESTINO"
echo "   sudo mkdir -p $PASTA_DESTINO/database"
echo "   sudo mkdir -p $PASTA_DESTINO/storage/logs"
echo "   sudo mkdir -p $PASTA_DESTINO/storage/framework/cache"
echo "   sudo mkdir -p $PASTA_DESTINO/storage/framework/sessions"
echo "   sudo mkdir -p $PASTA_DESTINO/storage/framework/views"
echo "   sudo chown -R darley:darley $PASTA_DESTINO"
echo ""
echo "3. Volte para este terminal e pressione ENTER para continuar..."
read -p "Pressione ENTER quando terminar..."

echo "📦 Preparando arquivos para upload..."

# Criar arquivo .env para produção
cat > .env.production << EOF
APP_NAME="API MQTT"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://10.100.0.21:8000

DB_CONNECTION=sqlite
DB_DATABASE=$PASTA_DESTINO/database/database.sqlite

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
MQTT_HOST=localhost
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_CLIENT_ID=laravel_mqtt_client_production
EOF

echo "📤 Fazendo upload dos arquivos para o servidor MQTT..."
# Upload dos arquivos para o servidor MQTT
sshpass -p "$SENHA_MQTT" scp -o StrictHostKeyChecking=no -r \
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
    "$USUARIO_MQTT@$SERVIDOR_MQTT:$PASTA_DESTINO/"

echo "✅ Upload dos arquivos concluído!"

echo "🔧 Configurando aplicação no servidor MQTT..."
executar_mqtt "cd $PASTA_DESTINO && mv .env.production .env"
echo "✅ Arquivo .env configurado"

executar_mqtt "cd $PASTA_DESTINO && composer install --no-dev --optimize-autoloader"
echo "✅ Dependências instaladas"

executar_mqtt "cd $PASTA_DESTINO && php artisan key:generate"
echo "✅ Chave da aplicação gerada"

executar_mqtt "cd $PASTA_DESTINO && touch database/database.sqlite"
executar_mqtt "cd $PASTA_DESTINO && php artisan migrate --force"
echo "✅ Banco de dados configurado"

executar_mqtt "cd $PASTA_DESTINO && chmod -R 755 storage"
executar_mqtt "cd $PASTA_DESTINO && chmod -R 755 bootstrap/cache"
echo "✅ Permissões configuradas"

echo "🔧 Verificando status do Mosquitto..."
executar_mqtt "sudo systemctl status mosquitto || sudo systemctl start mosquitto"
echo "✅ Mosquitto verificado/iniciado"

echo "🚀 Criando script de inicialização..."
cat > start_api_mqtt.sh << 'EOF'
#!/bin/bash
cd /root/api-mqtt
php artisan serve --host=0.0.0.0 --port=8000
EOF

sshpass -p "$SENHA_MQTT" scp -o StrictHostKeyChecking=no start_api_mqtt.sh "$USUARIO_MQTT@$SERVIDOR_MQTT:$PASTA_DESTINO/"
executar_mqtt "cd $PASTA_DESTINO && chmod +x start_api_mqtt.sh"
echo "✅ Script de inicialização criado"

echo "🔧 Criando serviço systemd..."
cat > api-mqtt.service << EOF
[Unit]
Description=API MQTT Laravel
After=network.target mosquitto.service

[Service]
Type=simple
User=root
WorkingDirectory=$PASTA_DESTINO
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
Restart=always
RestartSec=10
Environment=PATH=/usr/bin:/usr/local/bin

[Install]
WantedBy=multi-user.target
EOF

sshpass -p "$SENHA_MQTT" scp -o StrictHostKeyChecking=no api-mqtt.service "$USUARIO_MQTT@$SERVIDOR_MQTT:/tmp/"
echo "📝 Execute os seguintes comandos sudo no servidor MQTT:"
echo "   sudo mv /tmp/api-mqtt.service /etc/systemd/system/"
echo "   sudo systemctl daemon-reload"
echo "   sudo systemctl enable api-mqtt"
echo "   sudo systemctl start api-mqtt"
echo ""
echo "Pressione ENTER quando terminar..."
read -p "Pressione ENTER quando terminar..."

echo "🧪 Testando a API..."
sleep 5

# Teste básico da API
if curl -s --connect-timeout 10 http://10.100.0.21:8000/api/mqtt/topics > /dev/null; then
    echo "✅ API está funcionando!"
else
    echo "⚠️ API pode não estar respondendo ainda. Aguarde alguns segundos."
fi

echo "📋 Criando script de teste..."
cat > teste_api_mqtt.sh << 'EOF'
#!/bin/bash
echo "🧪 Testando endpoints da API MQTT..."

echo "1. Testando listagem de tópicos:"
curl -X GET http://10.100.0.21:8000/api/mqtt/topics

echo -e "\n\n2. Testando criação de tópico:"
curl -X POST http://10.100.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "teste/mqtt", "description": "Teste no servidor MQTT"}'

echo -e "\n\n3. Testando envio de mensagem:"
curl -X POST http://10.100.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "teste/mqtt", "mensagem": "deploy_sucesso"}'

echo -e "\n\n✅ Testes concluídos!"
EOF

chmod +x teste_api_mqtt.sh

echo "🎉 Deploy no servidor MQTT concluído com sucesso!"
echo ""
echo "📋 Resumo do deploy:"
echo "  ✅ API instalada em: $PASTA_DESTINO"
echo "  ✅ Serviço systemd criado: api-mqtt"
echo "  ✅ API rodando em: http://10.100.0.21:8000"
echo "  ✅ Conectado ao Mosquitto local"
echo ""
echo "🔧 Comandos úteis:"
echo "  - Verificar status: sudo systemctl status api-mqtt"
echo "  - Reiniciar API: sudo systemctl restart api-mqtt"
echo "  - Ver logs: sudo journalctl -u api-mqtt -f"
echo "  - Testar API: ./teste_api_mqtt.sh"
echo ""
echo "📡 Endpoints disponíveis:"
echo "  - GET  http://10.100.0.21:8000/api/mqtt/topics"
echo "  - POST http://10.100.0.21:8000/api/mqtt/topics"
echo "  - POST http://10.100.0.21:8000/api/mqtt/send-message" 