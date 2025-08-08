#!/bin/bash

# Script de Deploy da API MQTT Laravel via servidor MQTT
# Servidor MQTT: 10.100.0.21 (intermediário)
# Servidor de destino: 10.100.0.200

set -e

echo "🚀 Iniciando deploy da API MQTT Laravel via servidor MQTT..."

# Configurações
SERVIDOR_MQTT="10.100.0.21"
USUARIO_MQTT="darley"
SENHA_MQTT="yhvh77"

SERVIDOR_DESTINO="10.100.0.200"
USUARIO_DESTINO="roboflex"
SENHA_DESTINO="Roboflex()123"
PASTA_DESTINO="/root/api-mqtt"

echo "📋 Configurações:"
echo "  - Servidor MQTT (intermediário): $SERVIDOR_MQTT"
echo "  - Usuário MQTT: $USUARIO_MQTT"
echo "  - Servidor destino: $SERVIDOR_DESTINO"
echo "  - Usuário destino: $USUARIO_DESTINO"
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

echo "🔧 Verificando conectividade com o servidor de destino via MQTT..."
if ! executar_mqtt "sshpass -p '$SENHA_DESTINO' ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no '$USUARIO_DESTINO@$SERVIDOR_DESTINO' 'echo \"SSH OK\"'"; then
    echo "❌ Erro: Servidor MQTT não consegue acessar o servidor de destino via SSH"
    echo "🔧 Tentando verificar se o servidor de destino está acessível..."
    
    # Verificar se o servidor MQTT tem sshpass instalado
    if ! executar_mqtt "which sshpass"; then
        echo "📦 Instalando sshpass no servidor MQTT..."
        executar_mqtt "sudo apt update && sudo apt install -y sshpass"
    fi
    
    # Tentar novamente
    if ! executar_mqtt "sshpass -p '$SENHA_DESTINO' ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no '$USUARIO_DESTINO@$SERVIDOR_DESTINO' 'echo \"SSH OK\"'"; then
        echo "❌ Ainda não foi possível conectar ao servidor de destino"
        echo "🔧 Vamos tentar uma abordagem diferente..."
    fi
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

echo "📤 Fazendo upload dos arquivos para o servidor MQTT..."
# Upload dos arquivos para o servidor MQTT primeiro
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
    "$USUARIO_MQTT@$SERVIDOR_MQTT:/tmp/api-mqtt/"

echo "🔧 Configurando deploy via servidor MQTT..."

# Criar script de deploy no servidor MQTT
cat > deploy_remote.sh << 'EOF'
#!/bin/bash

SERVIDOR_DESTINO="10.100.0.200"
USUARIO_DESTINO="roboflex"
SENHA_DESTINO="Roboflex()123"
PASTA_DESTINO="/root/api-mqtt"

echo "🔧 Tentando diferentes métodos de acesso ao servidor de destino..."

# Método 1: Tentar SSH direto
if sshpass -p "$SENHA_DESTINO" ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "echo 'SSH direto OK'"; then
    echo "✅ SSH direto funcionando"
    
    # Criar estrutura de pastas
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "mkdir -p $PASTA_DESTINO"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "mkdir -p $PASTA_DESTINO/database"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "mkdir -p $PASTA_DESTINO/storage/logs"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "mkdir -p $PASTA_DESTINO/storage/framework/cache"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "mkdir -p $PASTA_DESTINO/storage/framework/sessions"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "mkdir -p $PASTA_DESTINO/storage/framework/views"
    
    # Upload dos arquivos
    sshpass -p "$SENHA_DESTINO" scp -o StrictHostKeyChecking=no -r /tmp/api-mqtt/* "$USUARIO_DESTINO@$SERVIDOR_DESTINO:$PASTA_DESTINO/"
    
    # Configurar aplicação
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "cd $PASTA_DESTINO && mv .env.production .env"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "cd $PASTA_DESTINO && composer install --no-dev --optimize-autoloader"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "cd $PASTA_DESTINO && php artisan key:generate"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "cd $PASTA_DESTINO && touch database/database.sqlite"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "cd $PASTA_DESTINO && php artisan migrate --force"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "cd $PASTA_DESTINO && chmod -R 755 storage"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "cd $PASTA_DESTINO && chmod -R 755 bootstrap/cache"
    
    # Criar serviço systemd
    cat > api-mqtt.service << 'SERVICE_EOF'
[Unit]
Description=API MQTT Laravel
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root/api-mqtt
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
SERVICE_EOF
    
    sshpass -p "$SENHA_DESTINO" scp -o StrictHostKeyChecking=no api-mqtt.service "$USUARIO_DESTINO@$SERVIDOR_DESTINO:/tmp/"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "sudo mv /tmp/api-mqtt.service /etc/systemd/system/"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "sudo systemctl daemon-reload"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "sudo systemctl enable api-mqtt"
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "sudo systemctl start api-mqtt"
    
    echo "✅ Deploy via SSH direto concluído!"
    
else
    echo "❌ SSH direto não funcionou"
    echo "🔧 Tentando método alternativo..."
    
    # Método 2: Usar netcat ou outras ferramentas
    echo "📋 Verificando se o servidor de destino tem serviços web ativos..."
    
    # Verificar se há um servidor web rodando
    if curl -s --connect-timeout 5 http://10.100.0.200:8000 > /dev/null; then
        echo "✅ Servidor web já está rodando na porta 8000"
    else
        echo "⚠️ Servidor web não está rodando na porta 8000"
    fi
    
    # Tentar outras portas comuns
    for porta in 80 443 8080 3000 5000; do
        if curl -s --connect-timeout 3 http://10.100.0.200:$porta > /dev/null; then
            echo "✅ Servidor web encontrado na porta $porta"
        fi
    done
    
    echo "🔧 Tentando criar um arquivo compactado para transferência manual..."
    cd /tmp
    tar -czf api-mqtt.tar.gz api-mqtt/
    echo "📦 Arquivo compactado criado: /tmp/api-mqtt.tar.gz"
    echo "📋 Para instalar manualmente no servidor de destino:"
    echo "   1. Copie o arquivo /tmp/api-mqtt.tar.gz para o servidor de destino"
    echo "   2. Extraia: tar -xzf api-mqtt.tar.gz -C /root/"
    echo "   3. Configure: cd /root/api-mqtt && mv .env.production .env"
    echo "   4. Instale: composer install --no-dev --optimize-autoloader"
    echo "   5. Configure: php artisan key:generate && php artisan migrate --force"
    echo "   6. Inicie: php artisan serve --host=0.0.0.0 --port=8000"
fi

echo "🧪 Testando conectividade com a API..."
sleep 5

if curl -s --connect-timeout 10 http://10.100.0.200:8000/api/mqtt/topics > /dev/null; then
    echo "✅ API está funcionando!"
else
    echo "⚠️ API pode não estar respondendo ainda"
fi

echo "📋 Criando script de teste..."
cat > teste_api_remoto.sh << 'TESTE_EOF'
#!/bin/bash
echo "🧪 Testando endpoints da API..."

echo "1. Testando listagem de tópicos:"
curl -X GET http://10.100.0.200:8000/api/mqtt/topics

echo -e "\n\n2. Testando criação de tópico:"
curl -X POST http://10.100.0.200:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "teste/deploy", "description": "Teste após deploy"}'

echo -e "\n\n3. Testando envio de mensagem:"
curl -X POST http://10.100.0.200:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "teste/deploy", "mensagem": "deploy_sucesso"}'

echo -e "\n\n✅ Testes concluídos!"
TESTE_EOF

chmod +x teste_api_remoto.sh

echo "🎉 Deploy concluído!"
echo ""
echo "📋 Resumo:"
echo "  ✅ Arquivos enviados para o servidor MQTT"
echo "  ✅ Tentativa de deploy no servidor de destino"
echo "  ✅ Script de teste criado: teste_api_remoto.sh"
echo ""
echo "🔧 Para testar a API:"
echo "  ./teste_api_remoto.sh"
EOF

# Enviar e executar o script no servidor MQTT
sshpass -p "$SENHA_MQTT" scp -o StrictHostKeyChecking=no deploy_remote.sh "$USUARIO_MQTT@$SERVIDOR_MQTT:/tmp/"
executar_mqtt "chmod +x /tmp/deploy_remote.sh"
executar_mqtt "/tmp/deploy_remote.sh"

echo "🎉 Processo de deploy concluído!"
echo ""
echo "📋 Próximos passos:"
echo "  1. Verificar se a API está rodando: curl http://10.100.0.200:8000/api/mqtt/topics"
echo "  2. Se não estiver funcionando, verificar se o SSH está ativo no servidor de destino"
echo "  3. Configurar manualmente se necessário" 