<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chave do link "Meus horários" da cliente que agenda pela página do salão.
     * Fica vazia para quem só foi cadastrada pelo salão.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->unique()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('access_token');
        });
    }
};
