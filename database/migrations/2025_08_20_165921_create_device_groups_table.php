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
        Schema::create('device_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nome único do grupo
            $table->text('description')->nullable(); // Descrição opcional
            $table->string('color', 7)->default('#3B82F6'); // Cor do grupo (hex)
            $table->boolean('is_active')->default(true); // Status ativo/inativo
            $table->integer('sort_order')->default(0); // Ordem de exibição
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_groups');
    }
};
