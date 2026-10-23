<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tipo de negócio da empresa (salão, clínica de estética, barbearia...).
     * Fica vazio nas contas criadas antes do cadastro pedir essa informação.
     */
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->string('business_type')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn('business_type');
        });
    }
};
