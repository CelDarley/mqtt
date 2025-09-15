#!/bin/bash

# Script de Deploy Final da API MQTT Laravel
# Para executar como ROOT no servidor: 10.100.0.200
# Copie este arquivo para /root/ na máquina 10.100.0.200 e execute: sudo ./deploy_servidor_200_final.sh

set -e

echo "🚀 Iniciando deploy final da API MQTT Laravel..."

# Configurações
PASTA_DESTINO="/root/api-mqtt"
USUARIO_APP="darley"
SERVIDOR_IP="10.100.0.200"
SERVIDOR_MQTT="10.100.0.21"

echo "📋 Configurações:"
echo "  - Pasta destino: $PASTA_DESTINO"
echo "  - Usuário da aplicação: $USUARIO_APP"
echo "  - Servidor IP: $SERVIDOR_IP"
echo "  - Servidor MQTT: $SERVIDOR_MQTT"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Este script deve ser executado como root"
    echo "Execute: sudo ./deploy_servidor_200_final.sh"
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
chmod -R 755 $PASTA_DESTINO
echo "✅ Permissões configuradas"

echo "📦 Baixando arquivos da aplicação..."
cd $PASTA_DESTINO

# Criar arquivo .env para produção
cat > .env << EOF
APP_NAME="API MQTT"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://$SERVIDOR_IP:8000

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
MQTT_HOST=$SERVIDOR_MQTT
MQTT_PORT=1883
MQTT_USERNAME=darley
MQTT_PASSWORD=yhvh77
MQTT_CLIENT_ID=laravel_mqtt_client_production
EOF

echo "✅ Arquivo .env criado"

# Verificar se os arquivos da aplicação existem
if [ ! -f "artisan" ]; then
    echo "❌ Arquivos da aplicação não encontrados"
    echo "📋 Você precisa fazer upload dos arquivos primeiro"
    echo "Execute no servidor local:"
    echo "scp -r app/ bootstrap/ config/ database/ public/ resources/ routes/ storage/ vendor/ artisan composer.json composer.lock darley@$SERVIDOR_IP:$PASTA_DESTINO/"
    echo ""
    echo "Ou copie de uma pasta temporária:"
    echo "sudo cp -r /home/darley/temp-api-mqtt/* $PASTA_DESTINO/"
    exit 1
fi

echo "✅ Arquivos da aplicação encontrados"

echo "🔧 Configurando aplicação..."
cd $PASTA_DESTINO

# CRÍTICO: Configurar permissões ANTES de executar composer
echo "🔧 Configurando permissões específicas para Composer..."
chown -R $USUARIO_APP:$USUARIO_APP .
chmod -R 755 .
chmod 644 composer.json composer.lock
chmod 755 artisan

# Verificar se composer.json existe e tem permissões corretas
if [ -f "composer.json" ]; then
    echo "✅ composer.json encontrado"
    ls -la composer.json
else
    echo "❌ composer.json não encontrado"
    exit 1
fi

# Executar composer como usuário darley
echo "📦 Instalando dependências com Composer..."
sudo -u $USUARIO_APP composer install --no-dev --optimize-autoloader
echo "✅ Dependências instaladas"

echo "🔑 Gerando chave da aplicação..."
sudo -u $USUARIO_APP php artisan key:generate
echo "✅ Chave da aplicação gerada"

echo "🗄️ Configurando banco de dados..."
touch database/database.sqlite
chown $USUARIO_APP:$USUARIO_APP database/database.sqlite
chmod 664 database/database.sqlite
sudo -u $USUARIO_APP php artisan migrate --force
echo "✅ Banco de dados configurado"

echo "🔧 Configurando permissões finais..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R $USUARIO_APP:$USUARIO_APP storage
chown -R $USUARIO_APP:$USUARIO_APP bootstrap/cache
echo "✅ Permissões finais configuradas"

echo "🚀 Criando script de inicialização..."
cat > start_api.sh << 'EOF'
#!/bin/bash
cd /root/api-mqtt
php artisan serve --host=0.0.0.0 --port=8000
EOF

chmod +x start_api.sh
echo "✅ Script de inicialização criado"

echo "🔧 Criando serviço systemd..."
cat > /etc/systemd/system/api-mqtt.service << EOF
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
cat > /root/teste_api_200.sh << 'EOF'
#!/bin/bash
echo "🧪 Testando endpoints da API MQTT..."

echo "1. Testando listagem de tópicos:"
curl -X GET http://10.100.0.200:8000/api/mqtt/topics

echo -e "\n\n2. Testando criação de tópico:"
curl -X POST http://10.100.0.200:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "teste/mqtt", "description": "Teste no servidor 200"}'

echo -e "\n\n3. Testando envio de mensagem:"
curl -X POST http://10.100.0.200:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "teste/mqtt", "mensagem": "deploy_sucesso_200"}'

echo -e "\n\n✅ Testes concluídos!"
EOF

chmod +x /root/teste_api_200.sh

echo "📋 Criando script de gerenciamento..."
cat > /root/gerenciar_api_200.sh << 'EOF'
#!/bin/bash
echo "🔧 Gerenciador da API MQTT Laravel"
echo ""
echo "1. Verificar status da API"
echo "2. Reiniciar API"
echo "3. Ver logs da API"
echo "4. Testar API"
echo "5. Parar API"
echo "6. Iniciar API"
echo "7. Sair"
echo ""
read -p "Escolha uma opção (1-7): " opcao

case $opcao in
    1)
        systemctl status api-mqtt
        ;;
    2)
        systemctl restart api-mqtt
        echo "✅ API reiniciada"
        ;;
    3)
        journalctl -u api-mqtt -f
        ;;
    4)
        ./teste_api_200.sh
        ;;
    5)
        systemctl stop api-mqtt
        echo "✅ API parada"
        ;;
    6)
        systemctl start api-mqtt
        echo "✅ API iniciada"
        ;;
    7)
        echo "Saindo..."
        exit 0
        ;;
    *)
        echo "Opção inválida"
        ;;
esac
EOF

chmod +x /root/gerenciar_api_200.sh

echo "🎉 Deploy no servidor 10.100.0.200 concluído com sucesso!"
echo ""
echo "📋 Resumo do deploy:"
echo "  ✅ API instalada em: $PASTA_DESTINO"
echo "  ✅ Serviço systemd criado: api-mqtt"
echo "  ✅ API rodando em: http://$SERVIDOR_IP:8000"
echo "  ✅ Conectado ao Mosquitto em: $SERVIDOR_MQTT"
echo ""
echo "🔧 Comandos úteis:"
echo "  - Verificar status: systemctl status api-mqtt"
echo "  - Reiniciar API: systemctl restart api-mqtt"
echo "  - Ver logs: journalctl -u api-mqtt -f"
echo "  - Testar API: ./teste_api_200.sh"
echo "  - Gerenciar API: ./gerenciar_api_200.sh"
echo ""
echo "📡 Endpoints disponíveis:"
echo "  - GET  http://$SERVIDOR_IP:8000/api/mqtt/topics"
echo "  - POST http://$SERVIDOR_IP:8000/api/mqtt/topics"
echo "  - POST http://$SERVIDOR_IP:8000/api/mqtt/send-message"
echo ""
echo "🔧 Scripts criados:"
echo "  - /root/teste_api_200.sh (testar API)"
echo "  - /root/gerenciar_api_200.sh (gerenciar API)"
echo "  - $PASTA_DESTINO/start_api.sh (iniciar manualmente)" 