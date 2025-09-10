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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->unsignedBigInteger('id_comp')->nullable()->after('phone');
            $table->enum('tipo', ['admin', 'comum'])->default('comum')->after('id_comp');

            // Adicionar índice para busca por companhia
            $table->index('id_comp');

            // Adicionar chave estrangeira para companhia
            $table->foreign('id_comp')->references('id')->on('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_comp']);
            $table->dropIndex(['id_comp']);
            $table->dropColumn(['phone', 'id_comp', 'tipo']);
        });
    }
};

