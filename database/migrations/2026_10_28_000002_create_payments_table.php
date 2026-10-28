<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pagamento de um atendimento: é a "entrada" do caixa.
     * Valores e comissão ficam guardados como estavam no dia, mesmo que o preço
     * do serviço ou o % do profissional mudem depois.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            // Se o horário ou o profissional forem apagados, o dinheiro recebido continua no caixa
            $table->foreignId('appointment_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('professional_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description'); // ex.: "Juliana Alves — Coloração"
            $table->string('method'); // pix, cash, debit, credit
            $table->decimal('subtotal', 10, 2);
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->decimal('amount', 10, 2); // total pago, já com desconto
            $table->unsignedTinyInteger('installments')->default(1);
            $table->decimal('cash_received', 10, 2)->nullable(); // para calcular o troco
            $table->decimal('commission_percent', 5, 2);
            $table->decimal('commission', 10, 2);
            $table->dateTime('paid_at');
            $table->timestamps();

            $table->index(['salon_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
