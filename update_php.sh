#!/bin/bash

echo "=== ATUALIZANDO PHP NA MÁQUINA 10.102.0.21 ==="

# Adicionar repositório do PHP 8.2
sudo add-apt-repository ppa:ondrej/php -y

# Atualizar pacotes
sudo apt update

# Remover PHP 7.4
sudo apt remove --purge php7.4* -y

# Instalar PHP 8.2
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-json php8.2-dom php8.2-xmlreader php8.2-xmlwriter php8.2-tokenizer php8.2-opcache php8.2-fileinfo php8.2-ctype php8.2-phar php8.2-sqlite3

# Verificar versão
php --version

echo "=== PHP ATUALIZADO! ===" 