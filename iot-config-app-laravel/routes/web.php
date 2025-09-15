<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PendingDeviceController;

// Rota principal - redirecionar para dispositivos pendentes
Route::get('/', function () {
    return redirect()->route('pending-devices.index');
})->name('home');
Route::get('/about', function () {
    return view('about');
})->name('about');

// Rotas de verificação de dispositivo
Route::get('/api/device-status', [HomeController::class, 'getDeviceStatus'])->name('device.status');
Route::get('/api/check-device', [HomeController::class, 'checkDeviceConnection'])->name('device.check');

// Rotas de configuração de dispositivo
Route::get('/device/config', [DeviceController::class, 'config'])->name('device.config');
// Rota BLE removida - projeto não usa mais BLE
Route::get('/device/add', [DeviceController::class, 'add'])->name('device.add');
Route::get('/device/success', [DeviceController::class, 'success'])->name('device.success');
Route::get('/device/transition', [DeviceController::class, 'transition'])->name('device.transition');
Route::get('/device/transition-debug', function(Request $request) {
    $macAddress = $request->get('mac');
    $ssid = $request->get('ssid');
    return view('device.transition-debug', compact('macAddress', 'ssid'));
})->name('device.transition-debug');
Route::post('/device/save', [DeviceController::class, 'save'])->name('device.save');
Route::post('/device/save-json', [DeviceController::class, 'save'])->name('device.save-json');
Route::post('/device/save-topic', [DeviceController::class, 'saveTopic'])->name('device.save-topic');

// APIs para buscar listas
Route::get('/api/device-types', [DeviceController::class, 'getDeviceTypes'])->name('api.device-types');
Route::get('/api/departments', [DeviceController::class, 'getDepartments'])->name('api.departments');

// ====== ROTAS DE DISPOSITIVOS PENDENTES (SEM AUTENTICAÇÃO) ======
Route::prefix('admin/devices')->name('pending-devices.')->middleware('web')->group(function () {
    // Tela principal com lista de dispositivos pendentes
    Route::get('/pending', [PendingDeviceController::class, 'index'])->name('index');
    
    // Detalhes de um dispositivo
    Route::get('/pending/{id}', [PendingDeviceController::class, 'show'])->name('show');
    
    // Formulário de ativação
    Route::get('/pending/{id}/activate', [PendingDeviceController::class, 'activate'])->name('activate');
    Route::post('/pending/{id}/activate', [PendingDeviceController::class, 'processActivation'])->name('process-activation');
    
    // Ações rápidas
    Route::post('/pending/{id}/reject', [PendingDeviceController::class, 'reject'])->name('reject');
    Route::delete('/pending/{id}', [PendingDeviceController::class, 'destroy'])->name('destroy');
});

// APIs AJAX para dispositivos pendentes
Route::prefix('api/pending-devices')->name('api.pending-devices.')->group(function () {
    Route::get('/stats', [PendingDeviceController::class, 'stats'])->name('stats');
    Route::post('/find-by-mac', [PendingDeviceController::class, 'findByMac'])->name('find-by-mac');
    Route::get('/refresh', [PendingDeviceController::class, 'refresh'])->name('refresh');
});
