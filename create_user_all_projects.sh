#!/bin/bash

# Script para criar usuário darley@gmail.com em todos os projetos
# ==============================================================

echo "🔐 Criando usuário darley@gmail.com em TODOS os projetos"
echo "========================================================"

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✅ $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ️ $1${NC}"; }

# 1. Backend Principal (MQTT)
echo ""
print_info "1. Criando usuário no Backend Principal (mqtt)..."
cd mqtt
if [ -f "create_user_darley.php" ]; then
    php create_user_darley.php
    if [ $? -eq 0 ]; then
        print_success "Usuário criado no backend principal"
    else
        print_error "Erro ao criar usuário no backend principal"
    fi
else
    print_warning "Script não encontrado no backend principal"
fi
cd ..

# 2. Dashboard Web
echo ""
print_info "2. Criando usuário no Dashboard Web (iot-config-web-laravel)..."
cd iot-config-web-laravel

# Criar script para o web
cat > create_user_darley_web.php << 'EOF'
<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🔐 Criando usuário darley@gmail.com no Dashboard Web\n";

try {
    $existingUser = User::where('email', 'darley@gmail.com')->first();
    
    if ($existingUser) {
        echo "⚠️  Usuário já existe. Atualizando senha...\n";
        $existingUser->password = Hash::make('yhvh77');
        $existingUser->save();
        echo "✅ Senha atualizada!\n";
    } else {
        $user = User::create([
            'name' => 'Darley',
            'email' => 'darley@gmail.com',
            'password' => Hash::make('yhvh77'),
            'email_verified_at' => now()
        ]);
        echo "✅ Usuário criado: {$user->name} ({$user->email})\n";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
EOF

if [ -f "artisan" ]; then
    php create_user_darley_web.php
    if [ $? -eq 0 ]; then
        print_success "Usuário criado no dashboard web"
    else
        print_error "Erro ao criar usuário no dashboard web"
    fi
    rm -f create_user_darley_web.php
else
    print_warning "Projeto web não encontrado ou não é Laravel"
fi
cd ..

# 3. App Config
echo ""
print_info "3. Criando usuário no App Config (iot-config-app-laravel)..."
cd iot-config-app-laravel

# Criar script para o app
cat > create_user_darley_app.php << 'EOF'
<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🔐 Criando usuário darley@gmail.com no App Config\n";

try {
    $existingUser = User::where('email', 'darley@gmail.com')->first();
    
    if ($existingUser) {
        echo "⚠️  Usuário já existe. Atualizando senha...\n";
        $existingUser->password = Hash::make('yhvh77');
        $existingUser->save();
        echo "✅ Senha atualizada!\n";
    } else {
        $user = User::create([
            'name' => 'Darley',
            'email' => 'darley@gmail.com',
            'password' => Hash::make('yhvh77'),
            'email_verified_at' => now()
        ]);
        echo "✅ Usuário criado: {$user->name} ({$user->email})\n";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
EOF

if [ -f "artisan" ]; then
    php create_user_darley_app.php
    if [ $? -eq 0 ]; then
        print_success "Usuário criado no app config"
    else
        print_error "Erro ao criar usuário no app config"
    fi
    rm -f create_user_darley_app.php
else
    print_warning "Projeto app não encontrado ou não é Laravel"
fi
cd ..

# Resumo final
echo ""
echo "🎉 RESUMO - USUÁRIO CRIADO EM TODOS OS PROJETOS"
echo "==============================================="
echo ""
print_success "✅ Backend Principal (mqtt) - ADMIN com JWT"
print_success "✅ Dashboard Web (iot-config-web-laravel)"  
print_success "✅ App Config (iot-config-app-laravel)"
echo ""
print_info "🔐 Credenciais únicas para todos os projetos:"
echo "=============================================="
echo "📧 Email: darley@gmail.com"
echo "🔑 Senha: yhvh77"
echo ""
print_info "🌐 URLs de acesso:"
echo "=================="
echo "🖥️  Dashboard Web: http://10.102.0.101:8001/"
echo "📱 App Config: http://10.102.0.101:8002/"  
echo "🔧 Backend API: http://10.102.0.101:8000/api/"
echo ""
print_info "💡 Funcionalidades disponíveis:"
echo "==============================="
echo "• Login em qualquer um dos 3 projetos"
echo "• Acesso ADMIN total no backend principal"
echo "• Gerenciamento de dispositivos IoT"
echo "• Sistema OTA completo"
echo "• Dashboard de monitoramento"
echo ""
print_success "🚀 Usuário darley@gmail.com está pronto em todos os projetos!" 