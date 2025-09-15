#!/bin/bash

# Script para fazer upload dos arquivos para o servidor 10.100.0.200
# Usuário: darley
# Pasta: /root/api-mqtt

echo "📤 Fazendo upload dos arquivos para o servidor 10.100.0.200..."

# Configurações
SERVIDOR_DESTINO="10.100.0.200"
USUARIO_DESTINO="darley"
SENHA_DESTINO="Roboflex()123"
PASTA_DESTINO="/root/api-mqtt"
PASTA_TEMP="/home/darley/temp-api-mqtt"

echo "📋 Configurações:"
echo "  - Servidor destino: $SERVIDOR_DESTINO"
echo "  - Usuário: $USUARIO_DESTINO"
echo "  - Pasta destino: $PASTA_DESTINO"
echo "  - Pasta temporária: $PASTA_TEMP"

# Função para executar comandos no servidor de destino
executar_remoto() {
    sshpass -p "$SENHA_DESTINO" ssh -o StrictHostKeyChecking=no "$USUARIO_DESTINO@$SERVIDOR_DESTINO" "$1"
}

echo "🔧 Verificando conectividade com o servidor..."
if ! executar_remoto "echo 'Conexão OK'"; then
    echo "❌ Erro: Não foi possível conectar ao servidor $SERVIDOR_DESTINO"
    echo "🔧 Verificando se o SSH está ativo..."
    nmap -p 22 $SERVIDOR_DESTINO
    exit 1
fi

echo "✅ Conexão com servidor estabelecida!"

echo "🗂️ Criando pasta temporária no servidor..."
executar_remoto "mkdir -p $PASTA_TEMP"

echo "📤 Fazendo upload dos arquivos para pasta temporária..."
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
    "$USUARIO_DESTINO@$SERVIDOR_DESTINO:$PASTA_TEMP/"

echo "✅ Upload dos arquivos concluído!"
echo ""
echo "📋 Próximos passos:"
echo "1. Conecte ao servidor: ssh darley@10.100.0.200"
echo "2. Execute os comandos como root:"
echo "   sudo mkdir -p $PASTA_DESTINO"
echo "   sudo cp -r $PASTA_TEMP/* $PASTA_DESTINO/"
echo "   sudo chown -R darley:darley $PASTA_DESTINO"
echo "3. Copie o script de deploy:"
echo "   scp deploy_servidor_200.sh darley@10.100.0.200:/home/darley/"
echo "4. Execute o deploy como root:"
echo "   sudo cp /home/darley/deploy_servidor_200.sh /root/"
echo "   cd /root"
echo "   sudo ./deploy_servidor_200.sh"
echo ""
echo "🔧 Ou execute tudo de uma vez:"
echo "   sudo ./deploy_servidor_200.sh" 