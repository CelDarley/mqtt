#!/bin/bash

echo "=== INSTALANDO PHP 8.2 ==="

# Adicionar repositório do PHP 8.2
echo "Adicionando repositório do PHP 8.2..."
sudo add-apt-repository ppa:ondrej/php -y

# Atualizar pacotes
echo "Atualizando pacotes..."
sudo apt update

# Verificar se o repositório foi adicionado
echo "Verificando repositórios disponíveis..."
apt list --upgradable | grep php8.2

# Instalar PHP 8.2
echo "Instalando PHP 8.2..."
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-json php8.2-dom php8.2-xmlreader php8.2-xmlwriter php8.2-tokenizer php8.2-opcache php8.2-fileinfo php8.2-ctype php8.2-phar php8.2-sqlite3

# Verificar versão
echo "Versão do PHP instalada:"
php --version

echo "=== PHP 8.2 INSTALADO! ===" 