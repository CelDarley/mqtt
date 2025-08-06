<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopicController;

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
    
    // Enviar mensagem para tópico
    Route::post('/send-message', [TopicController::class, 'sendMessage']);
});
