#!/bin/bash

# Script para instalar PHP no servidor 10.100.0.200 (Corrigido)
# Execute como: sudo ./instalar_php_corrigido_200.sh

set -e

echo "🔧 Instalando PHP no servidor 10.100.0.200..."

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Este script deve ser executado como root"
    echo "Execute: sudo ./instalar_php_corrigido_200.sh"
    exit 1
fi

echo "✅ Executando como root"

echo "📦 Atualizando repositórios..."
apt update

echo "🔧 Instalando repositório oficial do PHP..."
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

echo "🔧 Instalando PHP 8.2 CLI (inclui json e outras extensões básicas)..."
apt install -y php8.2-cli

echo "🔧 Instalando extensões PHP adicionais..."
apt install -y php8.2-common php8.2-mysql php8.2-zip \
    php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
    php8.2-dom php8.2-xmlreader php8.2-xmlwriter \
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

echo "🔧 Verificando extensões PHP instaladas..."
php -m | grep -E "(json|mbstring|curl|xml|bcmath|dom|tokenizer|opcache|fileinfo|ctype|phar|sqlite3|intl)"

echo "🎉 Requisitos instalados com sucesso!"
echo ""
echo "📋 Agora você pode executar o deploy:"
echo "   sudo ./deploy_servidor_200.sh" 