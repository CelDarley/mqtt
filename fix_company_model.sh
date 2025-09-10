#!/bin/bash

echo "🔧 Enviando Company.php corrigido para o servidor..."

# Configurações
SERVIDOR="10.100.0.200"
USUARIO="darley"
SENHA="yhvh77"

echo "📋 Configurações:"
echo "- Servidor: $SERVIDOR"
echo "- Usuário: $USUARIO"
echo "- Arquivo: Company.php corrigido"

# Enviar arquivo corrigido
echo "📦 Enviando arquivo..."
sshpass -p "$SENHA" scp app/Models/Company.php "$USUARIO@$SERVIDOR:/tmp/Company.php"

# Copiar para o servidor
echo "🔐 Copiando para o servidor..."
sshpass -p "$SENHA" ssh "$USUARIO@$SERVIDOR" "echo '$SENHA' | sudo -S cp /tmp/Company.php /root/api-mqtt/app/Models/Company.php"

# Limpar arquivo temporário
echo "🧹 Limpando arquivo temporário..."
sshpass -p "$SENHA" ssh "$USUARIO@$SERVIDOR" "rm -f /tmp/Company.php"

echo "✅ Company.php corrigido enviado com sucesso!"
echo "🚀 Agora teste novamente a API!"
