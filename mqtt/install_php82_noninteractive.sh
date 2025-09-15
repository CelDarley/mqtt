#!/bin/bash

echo "=== INSTALANDO PHP 8.2 SEM INTERAÇÃO ==="

# Configurar sudo para não pedir senha temporariamente
echo "Configurando sudo..."
echo "darley ALL=(ALL) NOPASSWD:ALL" | sudo tee /etc/sudoers.d/darley_temp

# Adicionar repositório do Ondřej Surý
echo "Adicionando repositório do Ondřej Surý..."
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y

# Atualizar pacotes
echo "Atualizando pacotes..."
sudo apt update

# Verificar se o repositório foi adicionado
echo "Verificando repositórios..."
apt search php8.2

# Instalar PHP 8.2
echo "Instalando PHP 8.2..."
sudo apt install -y php8.2

# Instalar extensões necessárias
echo "Instalando extensões PHP..."
sudo apt install -y php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-json php8.2-dom php8.2-xmlreader php8.2-xmlwriter php8.2-tokenizer php8.2-opcache php8.2-fileinfo php8.2-ctype php8.2-phar php8.2-sqlite3

# Configurar PHP 8.2 como padrão
echo "Configurando PHP 8.2 como padrão..."
sudo update-alternatives --set php /usr/bin/php8.2

# Verificar versão
echo "Versão do PHP instalada:"
php --version

# Remover configuração temporária do sudo
sudo rm /etc/sudoers.d/darley_temp

echo "=== PHP 8.2 INSTALADO! ===" 