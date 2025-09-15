<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tipo')->default('comum')->after('email');
            $table->integer('id_comp')->nullable()->after('tipo');
            $table->boolean('is_active')->default(true)->after('id_comp');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'id_comp', 'is_active']);
        });
    }
};

