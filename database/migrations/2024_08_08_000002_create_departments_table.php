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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('nivel_hierarquico');
            $table->unsignedBigInteger('id_unid_up')->nullable();
            $table->unsignedBigInteger('id_comp');
            $table->timestamps();

            // Índices para performance
            $table->index(['id_comp', 'nivel_hierarquico']);
            $table->index(['id_comp', 'id_unid_up']);
            $table->index('id_comp');

            // Chaves estrangeiras
            $table->foreign('id_comp')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('id_unid_up')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
