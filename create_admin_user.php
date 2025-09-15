<?php

/**
 * Script para criar usuário administrador
 * Executa criação via API do projeto MQTT
 * 
 * Uso: php create_admin_user.php
 */

echo "\n=== CRIADOR DE USUÁRIO ADMINISTRADOR ===\n\n";

// Verificar se está no diretório correto
if (!file_exists('mqtt/app/Models/User.php')) {
    echo "❌ ERRO: Execute este script no diretório raiz do projeto (~/mqtt/)\n";
    echo "Estrutura esperada:\n";
    echo "  ~/mqtt/\n";
    echo "    ├── mqtt/\n";
    echo "    ├── iot-config-app-laravel/\n";
    echo "    └── iot-config-web-laravel/\n\n";
    exit(1);
}

// Função para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para fazer requisição POST
function makeApiRequest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => 0];
    }
    
    return ['response' => $response, 'http_code' => $httpCode];
}

// Verificar se a API está rodando
echo "🔍 Verificando se a API MQTT está rodando...\n";
$apiUrl = 'http://localhost:8000/api/users';
$testResponse = makeApiRequest($apiUrl, []);

if ($testResponse['http_code'] === 0) {
    echo "❌ ERRO: Não foi possível conectar com a API MQTT\n";
    echo "💡 SOLUÇÃO:\n";
    echo "   1. Vá para o diretório: cd mqtt/\n";
    echo "   2. Execute: php artisan serve\n";
    echo "   3. Aguarde a mensagem: 'Laravel development server started'\n";
    echo "   4. Execute este script novamente\n\n";
    exit(1);
}

echo "✅ API MQTT está respondendo!\n\n";

// Coleta de dados do usuário
echo "📝 Vamos criar um usuário administrador...\n\n";

// Nome
do {
    echo "👤 Nome completo do administrador: ";
    $name = trim(fgets(STDIN));
    if (empty($name)) {
        echo "❌ O nome não pode estar vazio!\n";
    }
} while (empty($name));

// Email
do {
    echo "📧 Email do administrador: ";
    $email = trim(fgets(STDIN));
    if (empty($email)) {
        echo "❌ O email não pode estar vazio!\n";
    } elseif (!isValidEmail($email)) {
        echo "❌ Email inválido! Use um formato como: admin@empresa.com\n";
    }
} while (empty($email) || !isValidEmail($email));

// Senha
do {
    echo "🔒 Senha (mínimo 6 caracteres): ";
    $password = trim(fgets(STDIN));
    if (strlen($password) < 6) {
        echo "❌ A senha deve ter pelo menos 6 caracteres!\n";
    }
} while (strlen($password) < 6);

// Confirmação da senha
do {
    echo "🔒 Confirme a senha: ";
    $confirmPassword = trim(fgets(STDIN));
    if ($password !== $confirmPassword) {
        echo "❌ As senhas não conferem!\n";
    }
} while ($password !== $confirmPassword);

// Telefone (opcional)
echo "📱 Telefone (opcional, pressione Enter para pular): ";
$phone = trim(fgets(STDIN));
if (empty($phone)) {
    $phone = null;
}

echo "\n📋 Dados do usuário:\n";
echo "   Nome: $name\n";
echo "   Email: $email\n";
echo "   Senha: " . str_repeat('*', strlen($password)) . "\n";
echo "   Telefone: " . ($phone ?: 'Não informado') . "\n";
echo "   Tipo: Administrador\n\n";

echo "❓ Confirma a criação do usuário? (s/N): ";
$confirmation = trim(fgets(STDIN));

if (strtolower($confirmation) !== 's' && strtolower($confirmation) !== 'sim') {
    echo "❌ Operação cancelada pelo usuário.\n\n";
    exit(0);
}

// Criar usuário via API
echo "\n🚀 Criando usuário administrador...\n";

$userData = [
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'tipo' => 'admin',
];

if ($phone) {
    $userData['phone'] = $phone;
}

$createResponse = makeApiRequest($apiUrl, $userData);

if ($createResponse['http_code'] === 0) {
    echo "❌ ERRO: Falha na comunicação com a API\n";
    echo "Erro: " . $createResponse['error'] . "\n\n";
    exit(1);
}

$responseData = json_decode($createResponse['response'], true);

if ($createResponse['http_code'] === 201 && $responseData['success']) {
    echo "✅ SUCESSO! Usuário administrador criado com sucesso!\n\n";
    echo "📋 Detalhes do usuário criado:\n";
    echo "   ID: " . $responseData['data']['id'] . "\n";
    echo "   Nome: " . $responseData['data']['name'] . "\n";
    echo "   Email: " . $responseData['data']['email'] . "\n";
    echo "   Tipo: " . $responseData['data']['tipo'] . "\n";
    echo "   Telefone: " . ($responseData['data']['phone'] ?: 'Não informado') . "\n";
    echo "   Criado em: " . $responseData['data']['created_at'] . "\n\n";
    
    echo "🎉 Agora você pode fazer login nos frontends com estas credenciais!\n\n";
    echo "📱 URLs dos frontends:\n";
    echo "   - App IoT: http://localhost:8001\n";
    echo "   - Web Admin: http://localhost:8002\n\n";
} else {
    echo "❌ ERRO ao criar usuário!\n";
    echo "Status HTTP: " . $createResponse['http_code'] . "\n";
    
    if ($responseData) {
        echo "Mensagem: " . ($responseData['message'] ?? 'Erro desconhecido') . "\n";
        
        if (isset($responseData['errors'])) {
            echo "Detalhes dos erros:\n";
            foreach ($responseData['errors'] as $field => $errors) {
                echo "  - $field: " . implode(', ', $errors) . "\n";
            }
        }
    } else {
        echo "Resposta: " . $createResponse['response'] . "\n";
    }
    echo "\n";
    exit(1);
}

echo "✨ Script finalizado com sucesso!\n\n"; 