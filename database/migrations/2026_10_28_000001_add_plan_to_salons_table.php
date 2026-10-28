<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Plano contratado pela empresa e o fim do teste grátis.
     * As contas criadas antes dos planos ficam no Essencial, sem teste.
     */
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->string('plan')->default('essencial')->after('business_type');
            $table->timestamp('trial_ends_at')->nullable()->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn(['plan', 'trial_ends_at']);
        });
    }
};
