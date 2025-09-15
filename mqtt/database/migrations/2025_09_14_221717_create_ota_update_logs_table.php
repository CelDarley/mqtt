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
        Schema::create('ota_update_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_type_id')->constrained('device_types')->onDelete('cascade');
            $table->string('firmware_version');
            $table->string('previous_version')->nullable();
            $table->integer('devices_count')->default(0);
            $table->enum('status', ['initiated', 'in_progress', 'completed', 'failed', 'cancelled'])->default('initiated');
            $table->json('device_results')->nullable(); // Resultados por dispositivo
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('firmware_url')->nullable();
            $table->string('checksum_md5')->nullable();
            $table->bigInteger('firmware_size_bytes')->nullable();
            $table->json('metadata')->nullable(); // Informações extras
            $table->timestamps();
            
            // Índices para performance
            $table->index(['device_type_id', 'status']);
            $table->index(['created_at']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ota_update_logs');
    }
};
