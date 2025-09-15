<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceTypeController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Http;

// Rotas de autenticação
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas de empresas temporariamente sem auth para debug
Route::resource('companies', CompanyController::class);
Route::get('companies/{id}/organizational-structure', [CompanyController::class, 'organizationalStructure'])->name('companies.organizational-structure');

// Rota de teste temporária (sem auth)
Route::get('test-companies', function() {
    try {
        $response = \Illuminate\Support\Facades\Http::get(config('app.api_base_url', 'http://localhost:8000/api') . '/mqtt/companies');
        if ($response->successful()) {
            $companies = $response->json()['data'] ?? [];
            return view('companies.test', compact('companies'));
        }
        return 'Erro na API: ' . $response->status() . ' - ' . $response->body();
    } catch (\Exception $e) {
        return 'Erro: ' . $e->getMessage();
    }
})->name('test.companies');

// Rotas de departamentos (temporariamente fora da autenticação para debug)
Route::resource('departments', DepartmentController::class);

// Rotas de usuários (temporariamente fora da autenticação para debug)
Route::get('/users', function () {
    return view('users.index');
})->name('users.index');

// Rotas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas de tipos de dispositivo
    Route::resource('device-types', DeviceTypeController::class);
    Route::patch('device-types/{id}/toggle-status', [DeviceTypeController::class, 'toggleStatus'])->name('device-types.toggle-status');
    
    // Rotas OTA (Over-The-Air Updates)
    Route::post('device-types/{id}/ota-update', [DeviceTypeController::class, 'otaUpdate'])->name('device-types.ota-update');
    Route::get('device-types/{id}/firmware-info', [DeviceTypeController::class, 'firmwareInfo'])->name('device-types.firmware-info');
    Route::get('ota-updates/{id}', [DeviceTypeController::class, 'otaStatus'])->name('ota-updates.status');
    Route::get('ota-updates', [DeviceTypeController::class, 'otaUpdates'])->name('ota-updates.index');

    // Rotas de tópicos MQTT
    Route::resource('topics', TopicController::class);
    Route::patch('topics/{id}/deactivate', [TopicController::class, 'deactivate'])->name('topics.deactivate');
    
    // Rotas de teste MQTT
    Route::post('/api/topics/test-connection', [TopicController::class, 'testConnection'])->name('topics.test-connection');
    Route::post('/api/topics/send-command', [TopicController::class, 'sendCommand'])->name('topics.send-command');

    // Rotas de dispositivos (placeholder)
    Route::get('/devices', function () {
        return view('devices.index');
    })->name('devices.index');
});
