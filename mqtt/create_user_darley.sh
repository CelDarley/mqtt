#!/bin/bash

# Script para criar usuário darley@gmail.com no Sistema MQTT IoT
# ==============================================================

echo "🔐 Criando usuário darley@gmail.com no Sistema MQTT IoT"
echo "======================================================="

# Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    echo "❌ Erro: Execute este script no diretório do projeto Laravel (mqtt)"
    echo "💡 Uso: cd mqtt && ./create_user_darley.sh"
    exit 1
fi

# Verificar dependências
if ! command -v php &> /dev/null; then
    echo "❌ PHP não encontrado. Instale o PHP primeiro."
    exit 1
fi

echo ""
echo "🚀 Executando script PHP para criar usuário..."
echo "=============================================="

# Executar script PHP
php create_user_darley.php

# Verificar resultado
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Usuário criado com sucesso!"
    echo ""
    echo "🌐 URLs de acesso:"
    echo "=================="
    echo "🖥️  Dashboard Web: http://10.102.0.101:8001/"
    echo "📱 App Config: http://10.102.0.101:8002/"
    echo "🔧 Backend API: http://10.102.0.101:8000/api/"
    echo ""
    echo "🔐 Credenciais:"
    echo "==============="
    echo "📧 Email: darley@gmail.com"
    echo "🔑 Senha: yhvh77"
    echo ""
    echo "💡 Dicas:"
    echo "========="
    echo "• Use as credenciais acima para fazer login no dashboard"
    echo "• O usuário foi criado como ADMIN com acesso total"
    echo "• Para testar OTA, acesse: Dashboard > Tipos de Dispositivo > Botão 🔄"
    echo "• Para ver logs OTA: Dashboard > 📊 Logs OTA"
    echo ""
else
    echo ""
    echo "❌ Erro ao criar usuário!"
    echo "💡 Verifique os logs acima para mais detalhes"
    exit 1
fi 