#!/bin/bash

echo "=== INSTALANDO PHP 8.2 COM SENHA ==="

# Função para executar comandos sudo com senha
sudo_with_password() {
    echo "yhvh77" | sudo -S $@
}

# Adicionar repositório do Ondřej Surý
echo "Adicionando repositório do Ondřej Surý..."
sudo_with_password apt install -y software-properties-common
echo "yhvh77" | sudo -S add-apt-repository ppa:ondrej/php -y

# Atualizar pacotes
echo "Atualizando pacotes..."
sudo_with_password apt update

# Verificar se o repositório foi adicionado
echo "Verificando repositórios..."
apt search php8.2

# Instalar PHP 8.2
echo "Instalando PHP 8.2..."
sudo_with_password apt install -y php8.2

# Instalar extensões necessárias
echo "Instalando extensões PHP..."
sudo_with_password apt install -y php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-json php8.2-dom php8.2-xmlreader php8.2-xmlwriter php8.2-tokenizer php8.2-opcache php8.2-fileinfo php8.2-ctype php8.2-phar php8.2-sqlite3

# Configurar PHP 8.2 como padrão
echo "Configurando PHP 8.2 como padrão..."
sudo_with_password update-alternatives --set php /usr/bin/php8.2

# Verificar versão
echo "Versão do PHP instalada:"
php --version

echo "=== PHP 8.2 INSTALADO! ===" 