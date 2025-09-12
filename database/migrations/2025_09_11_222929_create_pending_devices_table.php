<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pending_devices', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address', 17)->unique()->comment('MAC address do dispositivo');
            $table->string('device_name')->comment('Nome dado pelo usuário no captive portal');
            $table->string('ip_address', 15)->nullable()->comment('IP obtido na rede WiFi');
            $table->string('wifi_ssid')->nullable()->comment('SSID da rede WiFi conectada');
            $table->enum('status', ['pending', 'activated', 'rejected'])->default('pending')->comment('Status do dispositivo');
            $table->bigInteger('registered_at')->comment('Timestamp do registro (millis do ESP32)');
            $table->json('device_info')->nullable()->comment('Informações adicionais do dispositivo');
            $table->timestamp('activated_at')->nullable()->comment('Data/hora da ativação');
            $table->unsignedBigInteger('activated_by')->nullable()->comment('ID do usuário que ativou');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['status', 'created_at']);
            $table->index('mac_address');
            $table->index('registered_at');
            
            // Foreign key para usuários (se necessário)
            // $table->foreign('activated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_devices');
    }
};
