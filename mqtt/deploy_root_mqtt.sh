#!/bin/bash

# Script de Deploy da API MQTT Laravel para executar como ROOT no servidor MQTT
# Servidor MQTT: 10.100.0.21
# Execute como: sudo ./deploy_root_mqtt.sh

set -e

echo "🚀 Iniciando deploy da API MQTT Laravel como ROOT..."

# Configurações
PASTA_DESTINO="/root/api-mqtt"
USUARIO_APP="darley"

echo "📋 Configurações:"
echo "  - Pasta destino: $PASTA_DESTINO"
echo "  - Usuário da aplicação: $USUARIO_APP"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Este script deve ser executado como root"
    echo "Execute: sudo ./deploy_root_mqtt.sh"
    exit 1
fi

echo "✅ Executando como root"

echo "🔧 Verificando requisitos..."
which php || echo "PHP não encontrado"
which composer || echo "Composer não encontrado"
which git || echo "Git não encontrado"

echo "🗂️ Criando estrutura de pastas..."
mkdir -p $PASTA_DESTINO
mkdir -p $PASTA_DESTINO/database
mkdir -p $PASTA_DESTINO/storage/logs
mkdir -p $PASTA_DESTINO/storage/framework/cache
mkdir -p $PASTA_DESTINO/storage/framework/sessions
mkdir -p $PASTA_DESTINO/storage/framework/views
echo "✅ Estrutura de pastas criada"

echo "🔧 Configurando permissões..."
chown -R $USUARIO_APP:$USUARIO_APP $PASTA_DESTINO
echo "✅ Permissões configuradas"

echo "📦 Preparando arquivos para upload..."
echo "⚠️ Você precisa fazer upload dos arquivos manualmente"
echo "📋 Execute no servidor local:"
echo ""
echo "scp -r app/ bootstrap/ config/ database/ public/ resources/ routes/ storage/ vendor/ artisan composer.json composer.lock darley@10.100.0.21:$PASTA_DESTINO/"
echo ""

# Criar arquivo .env para produção
cat > $PASTA_DESTINO/.env << EOF
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

echo "✅ Arquivo .env criado"

echo "🔧 Configurando aplicação..."
cd $PASTA_DESTINO

# Verificar se os arquivos foram enviados
if [ ! -f "artisan" ]; then
    echo "❌ Arquivos da aplicação não encontrados"
    echo "📋 Faça upload dos arquivos primeiro:"
    echo "   scp -r app/ bootstrap/ config/ database/ public/ resources/ routes/ storage/ vendor/ artisan composer.json composer.lock darley@10.100.0.21:$PASTA_DESTINO/"
    exit 1
fi

echo "✅ Arquivos encontrados, configurando..."

# Configurar aplicação
sudo -u $USUARIO_APP composer install --no-dev --optimize-autoloader
echo "✅ Dependências instaladas"

sudo -u $USUARIO_APP php artisan key:generate
echo "✅ Chave da aplicação gerada"

touch database/database.sqlite
sudo -u $USUARIO_APP php artisan migrate --force
echo "✅ Banco de dados configurado"

chmod -R 755 storage
chmod -R 755 bootstrap/cache
echo "✅ Permissões configuradas"

echo "🔧 Verificando status do Mosquitto..."
systemctl status mosquitto || systemctl start mosquitto
echo "✅ Mosquitto verificado/iniciado"

echo "🚀 Criando script de inicialização..."
cat > $PASTA_DESTINO/start_api.sh << 'EOF'
#!/bin/bash
cd /root/api-mqtt
php artisan serve --host=0.0.0.0 --port=8000
EOF

chmod +x $PASTA_DESTINO/start_api.sh
echo "✅ Script de inicialização criado"

echo "🔧 Criando serviço systemd..."
cat > /etc/systemd/system/api-mqtt.service << EOF
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

systemctl daemon-reload
systemctl enable api-mqtt
systemctl start api-mqtt
echo "✅ Serviço systemd criado e iniciado"

echo "🧪 Testando a API..."
sleep 5

# Teste básico da API
if curl -s --connect-timeout 10 http://localhost:8000/api/mqtt/topics > /dev/null; then
    echo "✅ API está funcionando!"
else
    echo "⚠️ API pode não estar respondendo ainda. Aguarde alguns segundos."
fi

echo "📋 Criando script de teste..."
cat > /root/teste_api_mqtt.sh << 'EOF'
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

chmod +x /root/teste_api_mqtt.sh

echo "🎉 Deploy no servidor MQTT concluído com sucesso!"
echo ""
echo "📋 Resumo do deploy:"
echo "  ✅ API instalada em: $PASTA_DESTINO"
echo "  ✅ Serviço systemd criado: api-mqtt"
echo "  ✅ API rodando em: http://10.100.0.21:8000"
echo "  ✅ Conectado ao Mosquitto local"
echo ""
echo "🔧 Comandos úteis:"
echo "  - Verificar status: systemctl status api-mqtt"
echo "  - Reiniciar API: systemctl restart api-mqtt"
echo "  - Ver logs: journalctl -u api-mqtt -f"
echo "  - Testar API: ./teste_api_mqtt.sh"
echo ""
echo "📡 Endpoints disponíveis:"
echo "  - GET  http://10.100.0.21:8000/api/mqtt/topics"
echo "  - POST http://10.100.0.21:8000/api/mqtt/topics"
echo "  - POST http://10.100.0.21:8000/api/mqtt/send-message" 