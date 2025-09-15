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
        Schema::create('device_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id'); // ID do dispositivo (referência à tabela topics)
            $table->unsignedBigInteger('group_id')->nullable(); // ID do grupo (nullable para "sem grupo")
            $table->boolean('is_active')->default(true); // Status da associação
            $table->timestamp('assigned_at')->useCurrent(); // Data/hora da associação
            $table->timestamps();

            // Índices e chaves estrangeiras
            $table->foreign('device_id')->references('id')->on('topics')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('device_groups')->onDelete('set null');
            $table->unique(['device_id', 'group_id']); // Evita duplicatas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_group_assignments');
    }
};
