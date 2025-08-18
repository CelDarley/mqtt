#!/bin/bash

echo "=== CONFIGURANDO API NA MÁQUINA 10.102.0.21 ==="

# Ir para a pasta da API
cd /root/api-mqtt

# Verificar se o PHP está instalado
if ! command -v php &> /dev/null; then
    echo "PHP não está instalado. Instalando..."
    sudo apt update
    sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-json php8.2-dom php8.2-xmlreader php8.2-xmlwriter php8.2-tokenizer php8.2-opcache php8.2-fileinfo php8.2-ctype php8.2-phar php8.2-sqlite3
fi

# Verificar versão do PHP
echo "Versão do PHP:"
php --version

# Instalar dependências do Composer
echo "Instalando dependências..."
composer install --no-dev --optimize-autoloader

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Executar migrações
php artisan migrate

# Iniciar servidor
echo "Iniciando servidor na porta 8000..."
php artisan serve --host=0.0.0.0 --port=8000 