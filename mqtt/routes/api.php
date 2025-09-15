<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceTypeController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\DeviceGroupController;
use App\Http\Controllers\PendingDeviceController;
use App\Http\Controllers\OtaController;

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

// Autenticação
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');

// Rotas protegidas por autenticação JWT
Route::middleware(['auth:api'])->group(function () {
    // Usuários
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // Empresas
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{id}', [CompanyController::class, 'show']);
        Route::put('/{id}', [CompanyController::class, 'update']);
        Route::delete('/{id}', [CompanyController::class, 'destroy']);
    });

    // Departamentos
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{id}', [DepartmentController::class, 'show']);
        Route::put('/{id}', [DepartmentController::class, 'update']);
        Route::delete('/{id}', [DepartmentController::class, 'destroy']);
    });

    // Device Groups
    Route::prefix('device-groups')->group(function () {
        Route::get('/', [DeviceGroupController::class, 'index']);
        Route::post('/', [DeviceGroupController::class, 'store']);
        Route::get('/{id}', [DeviceGroupController::class, 'show']);
        Route::put('/{id}', [DeviceGroupController::class, 'update']);
        Route::delete('/{id}', [DeviceGroupController::class, 'destroy']);
    });

    // Pending Devices
    Route::prefix('pending-devices')->group(function () {
        Route::get('/', [PendingDeviceController::class, 'index']);
        Route::post('/', [PendingDeviceController::class, 'store']);
        Route::get('/{id}', [PendingDeviceController::class, 'show']);
        Route::put('/{id}', [PendingDeviceController::class, 'update']);
        Route::delete('/{id}', [PendingDeviceController::class, 'destroy']);
        Route::post('/{id}/activate', [PendingDeviceController::class, 'activate']);
    });
});

// Rotas MQTT (com prefixo específico)
Route::prefix('mqtt')->group(function () {
    // Topics
    Route::get('/topics', [TopicController::class, 'index']);
    Route::post('/topics', [TopicController::class, 'store']);
    Route::get('/topics/{id}', [TopicController::class, 'show']);
    Route::put('/topics/{id}', [TopicController::class, 'update']);
    Route::patch('/topics/{id}/deactivate', [TopicController::class, 'deactivate']);
    Route::delete('/topics/{id}', [TopicController::class, 'destroy']);

    // Device Types
    Route::get('/device-types', [\App\Http\Controllers\DeviceTypeController::class, 'index']);
    Route::post('/device-types', [\App\Http\Controllers\DeviceTypeController::class, 'store']);
    Route::get('/device-types/{id}', [\App\Http\Controllers\DeviceTypeController::class, 'show']);
    Route::put('/device-types/{id}', [\App\Http\Controllers\DeviceTypeController::class, 'update']);
    Route::delete('/device-types/{id}', [\App\Http\Controllers\DeviceTypeController::class, 'destroy']);
    Route::patch('/device-types/{id}/toggle-status', [\App\Http\Controllers\DeviceTypeController::class, 'toggleStatus']);
    
    // OTA - Firmware Updates
    Route::post('/device-types/{id}/ota-update', [OtaController::class, 'triggerUpdate']);
    Route::get('/device-types/{id}/firmware-info', [OtaController::class, 'getFirmwareInfo']);
    
    // OTA Updates Management
    Route::get('/ota-updates', [OtaController::class, 'listUpdates']);
    Route::get('/ota-updates/{id}', [OtaController::class, 'getUpdateStatus']);
    Route::get('/ota-updates/{id}/logs', [OtaController::class, 'getUpdateLogs']);
    Route::post('/ota-updates/{id}/cancel', [OtaController::class, 'cancelUpdate']);
    Route::post('/ota-updates/{id}/device-feedback', [OtaController::class, 'deviceFeedback']);
    
    // OTA Statistics
    Route::get('/ota-stats', [OtaController::class, 'getStats']);

    // Companies
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::get('/companies/{id}', [CompanyController::class, 'show']);
    Route::put('/companies/{id}', [CompanyController::class, 'update']);
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy']);

    // Companies
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::get('/companies/{id}', [CompanyController::class, 'show']);
    Route::put('/companies/{id}', [CompanyController::class, 'update']);
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy']);

    // Departments
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::get('/departments/{id}', [DepartmentController::class, 'show']);
    Route::put('/departments/{id}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);
});
