<?php

/**
 * Script para criar usuário darley@gmail.com
 * ==========================================
 * 
 * Este script cria o usuário específico no sistema MQTT IoT
 * com as credenciais fornecidas.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "🔐 Criando usuário darley@gmail.com\n";
echo "===================================\n\n";

try {
    // Verificar se o usuário já existe
    $existingUser = User::where('email', 'darley@gmail.com')->first();
    
    if ($existingUser) {
        echo "⚠️  Usuário darley@gmail.com já existe!\n";
        echo "🆔 ID: {$existingUser->id}\n";
        echo "👤 Nome: {$existingUser->name}\n";
        echo "📧 Email: {$existingUser->email}\n";
        echo "🏢 Empresa: " . ($existingUser->id_comp ? Company::find($existingUser->id_comp)->name ?? 'N/A' : 'N/A') . "\n";
        echo "🔑 Tipo: {$existingUser->tipo}\n";
        echo "📅 Criado em: {$existingUser->created_at}\n\n";
        
        $response = readline("Deseja atualizar a senha? (s/n): ");
        if (strtolower($response) === 's') {
            $existingUser->password = Hash::make('yhvh77');
            $existingUser->save();
            echo "✅ Senha atualizada com sucesso!\n";
        } else {
            echo "ℹ️  Operação cancelada.\n";
        }
        exit(0);
    }
    
    // Buscar ou criar empresa padrão
    $company = Company::where('name', 'Empresa Principal')->first();
    
    if (!$company) {
        echo "🏢 Criando empresa padrão...\n";
        $company = Company::create([
            'name' => 'Empresa Principal',
            'cnpj' => '12.345.678/0001-90',
            'address' => 'Endereço da Empresa',
            'phone' => '(11) 1234-5678',
            'email' => 'contato@empresa.com',
            'is_active' => true
        ]);
        echo "✅ Empresa criada: {$company->name}\n";
    }
    
    // Buscar ou criar departamento padrão
    $department = Department::where('id_comp', $company->id)
                            ->where('name', 'Administração')
                            ->first();
    
    if (!$department) {
        echo "🏛️  Criando departamento padrão...\n";
        $department = Department::create([
            'name' => 'Administração',
            'id_comp' => $company->id,
            'id_unid_up' => null,
            'nivel_hierarquico' => 1
        ]);
        echo "✅ Departamento criado: {$department->name}\n";
    }
    
    // Criar o usuário
    echo "👤 Criando usuário darley@gmail.com...\n";
    
    $user = User::create([
        'name' => 'Darley',
        'email' => 'darley@gmail.com',
        'password' => Hash::make('yhvh77'),
        'tipo' => 'admin', // Definir como admin
        'id_comp' => $company->id,
        'phone' => '(11) 99999-9999',
        'email_verified_at' => now()
    ]);
    
    echo "✅ Usuário criado com sucesso!\n\n";
    
    // Mostrar informações do usuário criado
    echo "📋 Informações do usuário criado:\n";
    echo "================================\n";
    echo "🆔 ID: {$user->id}\n";
    echo "👤 Nome: {$user->name}\n";
    echo "📧 Email: {$user->email}\n";
    echo "🔑 Senha: yhvh77\n";
    echo "🏢 Empresa: {$company->name}\n";
    echo "🏛️  Departamento: {$department->name}\n";
    echo "🔑 Tipo: {$user->tipo} (Administrador)\n";
    echo "📱 Telefone: {$user->phone}\n";
    echo "📅 Criado em: {$user->created_at}\n\n";
    
    // Informações de acesso
    echo "🌐 Informações de acesso:\n";
    echo "========================\n";
    echo "📱 Backend API: http://10.102.0.101:8000/api/\n";
    echo "🖥️  Dashboard Web: http://10.102.0.101:8001/\n";
    echo "📱 App Config: http://10.102.0.101:8002/\n\n";
    
    echo "🔐 Credenciais de login:\n";
    echo "========================\n";
    echo "Email: darley@gmail.com\n";
    echo "Senha: yhvh77\n\n";
    
    // Testar login JWT (se disponível)
    echo "🧪 Testando autenticação JWT...\n";
    try {
        $token = auth('api')->attempt([
            'email' => 'darley@gmail.com',
            'password' => 'yhvh77'
        ]);
        
        if ($token) {
            echo "✅ Autenticação JWT funcionando!\n";
            echo "🎫 Token JWT gerado com sucesso\n";
            echo "🔑 Token (primeiros 50 chars): " . substr($token, 0, 50) . "...\n";
        } else {
            echo "⚠️  Erro na autenticação JWT\n";
        }
    } catch (Exception $e) {
        echo "⚠️  JWT não disponível ou erro: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Script executado com sucesso!\n";
    echo "🚀 O usuário darley@gmail.com está pronto para uso!\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao criar usuário: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 