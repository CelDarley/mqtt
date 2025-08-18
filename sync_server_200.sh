#!/bin/bash

echo "🔄 Sincronizando código no servidor 10.100.0.200..."

# Configurações
SERVIDOR="10.100.0.200"
USUARIO="darley"
SENHA="yhvh77"
PASTA_TEMP="/tmp/api-mqtt-update"
PASTA_DESTINO="/root/api-mqtt"

echo "📋 Configurações:"
echo "- Servidor: $SERVIDOR"
echo "- Usuário: $USUARIO"
echo "- Pasta temporária: $PASTA_TEMP"
echo "- Pasta destino: $PASTA_DESTINO"

# Criar pasta temporária
echo "🗂️ Criando pasta temporária..."
sshpass -p "$SENHA" ssh "$USUARIO@$SERVIDOR" "mkdir -p $PASTA_TEMP"

# Transferir arquivos
echo "📦 Transferindo arquivos..."
sshpass -p "$SENHA" scp -r . "$USUARIO@$SERVIDOR:$PASTA_TEMP/"

# Executar comandos no servidor
echo "🔧 Executando comandos no servidor..."
sshpass -p "$SENHA" ssh "$USUARIO@$SERVIDOR" << 'EOF'
echo "📁 Verificando arquivos transferidos..."
ls -la /tmp/api-mqtt-update/

echo "🔐 Solicitando permissões sudo..."
echo "yhvh77" | sudo -S cp -r /tmp/api-mqtt-update/* /root/api-mqtt/

echo "✅ Verificando arquivos copiados..."
ls -la /root/api-mqtt/

echo "🧹 Limpando pasta temporária..."
rm -rf /tmp/api-mqtt-update

echo "🎯 Atualizando permissões..."
sudo chown -R darley:darley /root/api-mqtt
sudo chmod -R 755 /root/api-mqtt

echo "🚀 Executando migrações..."
cd /root/api-mqtt
sudo php artisan migrate --force

echo "✨ Sincronização concluída!"
EOF

echo "✅ Sincronização finalizada com sucesso!"
