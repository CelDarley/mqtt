<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API para registro de dispositivos IoT
Route::post('/devices/register', [DeviceController::class, 'registerDevice'])->name('api.devices.register'); 