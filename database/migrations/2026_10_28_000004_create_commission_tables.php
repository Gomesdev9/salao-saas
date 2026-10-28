<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vales (adiantamentos de comissão durante o mês) e pagamentos de comissão no fechamento.
     */
    public function up(): void
    {
        Schema::create('commission_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->date('given_on');
            $table->timestamps();

            $table->index(['salon_id', 'given_on']);
        });

        Schema::create('commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->date('month'); // sempre o dia 1 do mês a que a comissão se refere
            $table->decimal('amount', 10, 2);
            $table->dateTime('paid_at');
            $table->timestamps();

            $table->index(['salon_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_payouts');
        Schema::dropIfExists('commission_advances');
    }
};
