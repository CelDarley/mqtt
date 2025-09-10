<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas de Autenticação Simples
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Rotas para gerenciamento de tópicos MQTT
Route::prefix('mqtt')->group(function () {
    // Criar novo tópico
    Route::post('/topics', [TopicController::class, 'store']);

    // Listar todos os tópicos
    Route::get('/topics', [TopicController::class, 'index']);

    // Mostrar tópico específico
    Route::get('/topics/{id}', [TopicController::class, 'show']);

    // Desativar tópico
    Route::patch('/topics/{id}/deactivate', [TopicController::class, 'deactivate']);
    
    // Excluir tópico permanentemente
    Route::delete('/topics/{id}', [TopicController::class, 'destroy']);

    // Enviar mensagem para tópico
    Route::post('/send-message', [TopicController::class, 'sendMessage']);
    
    // Publicar comando MQTT (para interface web)
    Route::post('/publish', [TopicController::class, 'publishCommand']);
    
    // 🚀 NOVO: Processar auto-registro de dispositivos
    Route::post('/device-registration', [TopicController::class, 'processDeviceRegistration']);
    
    // Endpoint direto para comandos MQTT por tópico
    Route::post('/iot/{topic_path}', [TopicController::class, 'sendDirectCommand'])
        ->where('topic_path', '.*');
});

// Rotas para gerenciamento de Grupos de Dispositivos
Route::prefix('device-groups')->group(function () {
    // Listar todos os grupos
    Route::get('/', [App\Http\Controllers\DeviceGroupController::class, 'index']);

    // Criar novo grupo
    Route::post('/', [App\Http\Controllers\DeviceGroupController::class, 'store']);

    // Mostrar grupo específico
    Route::get('/{id}', [App\Http\Controllers\DeviceGroupController::class, 'show']);

    // Atualizar grupo
    Route::put('/{id}', [App\Http\Controllers\DeviceGroupController::class, 'update']);

    // Remover grupo
    Route::delete('/{id}', [App\Http\Controllers\DeviceGroupController::class, 'destroy']);

    // Atribuir dispositivo a um grupo
    Route::post('/assign-device', [App\Http\Controllers\DeviceGroupController::class, 'assignDevice']);

    // Listar dispositivos de um grupo
    Route::get('/{id}/devices', [App\Http\Controllers\DeviceGroupController::class, 'devices']);
});


// Rotas para gerenciamento de Companhias
Route::prefix('companies')->group(function () {
    // Listar todas as companhias
    Route::get('/', [CompanyController::class, 'index']);

    // Criar nova companhia
    Route::post('/', [CompanyController::class, 'store']);

    // Mostrar companhia específica
    Route::get('/{id}', [CompanyController::class, 'show']);

    // Atualizar companhia
    Route::put('/{id}', [CompanyController::class, 'update']);

    // Deletar companhia
    Route::delete('/{id}', [CompanyController::class, 'destroy']);

    // Obter estrutura organizacional da companhia
    Route::get('/{id}/structure', [CompanyController::class, 'organizationalStructure']);

    // Obter árvore organizacional da companhia
    Route::get('/{id}/tree', [CompanyController::class, 'organizationalTree']);
});

// Rotas para gerenciamento de Departamentos
Route::prefix('departments')->group(function () {
    // Listar todos os departamentos (com filtros)
    Route::get('/', [DepartmentController::class, 'index']);

    // Criar novo departamento
    Route::post('/', [DepartmentController::class, 'store']);

    // Mostrar departamento específico
    Route::get('/{id}', [DepartmentController::class, 'show']);

    // Atualizar departamento
    Route::put('/{id}', [DepartmentController::class, 'update']);

    // Deletar departamento
    Route::delete('/{id}', [DepartmentController::class, 'destroy']);

    // Obter hierarquia de um departamento
    Route::get('/{id}/hierarchy', [DepartmentController::class, 'hierarchy']);

    // Obter departamentos por companhia
    Route::get('/company/{companyId}', [DepartmentController::class, 'byCompany']);

    // Mover departamento na hierarquia
    Route::patch('/{id}/move', [DepartmentController::class, 'move']);
});

// Rotas para gerenciamento de Usuários
Route::prefix('users')->group(function () {
    // Listar todos os usuários (com filtros)
    Route::get('/', [UserController::class, 'index']);

    // Criar novo usuário
    Route::post('/', [UserController::class, 'store']);

    // Mostrar usuário específico
    Route::get('/{id}', [UserController::class, 'show']);

    // Atualizar usuário
    Route::put('/{id}', [UserController::class, 'update']);

    // Deletar usuário
    Route::delete('/{id}', [UserController::class, 'destroy']);

    // Trocar senha do usuário
    Route::patch('/{id}/change-password', [UserController::class, 'changePassword']);

    // Pesquisar usuários com filtros avançados
    Route::get('/search', [UserController::class, 'search']);

    // Listar usuários por companhia
    Route::get('/company/{companyId}', [UserController::class, 'byCompany']);

    // Estatísticas dos usuários
    Route::get('/stats', [UserController::class, 'stats']);
});
