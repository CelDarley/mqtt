#!/bin/bash

# Script para instalar requisitos no servidor 10.100.0.200
# Execute como: sudo ./instalar_requisitos_200.sh

set -e

echo "🔧 Instalando requisitos no servidor 10.100.0.200..."

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Este script deve ser executado como root"
    echo "Execute: sudo ./instalar_requisitos_200.sh"
    exit 1
fi

echo "✅ Executando como root"

echo "📦 Atualizando repositórios..."
apt update

echo "🔧 Instalando PHP e extensões..."
apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip \
    php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
    php8.2-json php8.2-dom php8.2-xmlreader php8.2-xmlwriter \
    php8.2-tokenizer php8.2-opcache php8.2-fileinfo php8.2-ctype \
    php8.2-phar php8.2-sqlite3 php8.2-intl

echo "✅ PHP instalado"

echo "🔧 Instalando Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

echo "✅ Composer instalado"

echo "🔧 Instalando outras dependências..."
apt install -y curl wget git unzip zip

echo "✅ Dependências instaladas"

echo "🔧 Verificando instalação..."
echo "PHP versão:"
php --version

echo "Composer versão:"
composer --version

echo "Git versão:"
git --version

echo "🎉 Requisitos instalados com sucesso!"
echo ""
echo "📋 Agora você pode executar o deploy:"
echo "   sudo ./deploy_servidor_200.sh" 