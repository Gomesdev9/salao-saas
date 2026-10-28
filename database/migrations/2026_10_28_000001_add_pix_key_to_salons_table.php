<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chave Pix que aparece na tela de receber pagamento.
     */
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->string('pix_key')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn('pix_key');
        });
    }
};
