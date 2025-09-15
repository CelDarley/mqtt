#!/bin/bash
# setup.sh - Setup completo do ambiente MQTT

set -e  # Parar em caso de erro

echo "🚀 Iniciando setup completo do ambiente MQTT..."

# Verificar pré-requisitos
if ! command -v php &> /dev/null; then
    echo "❌ PHP não encontrado. Instale PHP 8.2+ primeiro."
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo "❌ Composer não encontrado. Instale o Composer primeiro."
    exit 1
fi

if ! command -v npm &> /dev/null; then
    echo "❌ NPM não encontrado. Instale Node.js e NPM primeiro."
    exit 1
fi

# Função para setup de cada projeto
setup_project() {
    local project_dir=$1
    local project_name=$2
    local has_frontend=$3
    
    echo "📦 Configurando $project_name..."
    
    if [ ! -d "$project_dir" ]; then
        echo "❌ Diretório $project_dir não encontrado!"
        return 1
    fi
    
    cd "$project_dir"
    
    # Copiar .env se não existir
    if [ ! -f ".env" ]; then
        if [ -f ".env.example" ]; then
            cp .env.example .env
            echo "   ✅ Arquivo .env criado"
        else
            echo "   ⚠️  Arquivo .env.example não encontrado"
        fi
    else
        echo "   ℹ️  Arquivo .env já existe"
    fi
    
    # Instalar dependências PHP
    echo "   📥 Instalando dependências PHP..."
    composer install --no-interaction
    
    # Instalar dependências NPM se houver frontend
    if [ "$has_frontend" = true ] && [ -f "package.json" ]; then
        echo "   🎨 Instalando dependências NPM..."
        npm install
        echo "   🔨 Compilando assets..."
        npm run build
    fi
    
    # Gerar chave se não existir
    if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
        echo "   🔑 Gerando chave da aplicação..."
        php artisan key:generate --no-interaction
    else
        echo "   ℹ️  Chave da aplicação já existe"
    fi
    
    # Limpar caches
    echo "   🧹 Limpando caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    
    # Executar migrações
    echo "   🗄️  Executando migrações..."
    php artisan migrate --no-interaction
    
    # Ajustar permissões
    echo "   🔒 Ajustando permissões..."
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    
    echo "   ✅ $project_name configurado com sucesso!"
    cd ..
}

# Setup dos projetos
setup_project "mqtt" "Backend (API MQTT)" false
setup_project "iot-config-app-laravel" "Frontend App" true
setup_project "iot-config-web-laravel" "Frontend Web" true

echo ""
echo "🎉 Setup completo finalizado!"
echo ""
echo "📝 Próximos passos:"
echo "   1. Verificar/atualizar configurações nos arquivos .env"
echo "   2. Criar usuário administrador: php create_admin_user.php"
echo "   3. Iniciar servidores:"
echo "      - Backend: cd mqtt && php artisan serve"
echo "      - App: cd iot-config-app-laravel && php artisan serve --port=8001"
echo "      - Web: cd iot-config-web-laravel && php artisan serve --port=8002"
echo ""
echo "🌐 URLs dos serviços:"
echo "   - API: http://localhost:8000"
echo "   - App IoT: http://localhost:8001"
echo "   - Web Admin: http://localhost:8002"
echo "" 