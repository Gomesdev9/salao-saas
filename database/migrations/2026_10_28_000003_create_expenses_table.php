<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Despesas do salão (aluguel, luz, produtos...): são as "saídas" do caixa.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('category');
            $table->string('method')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->date('paid_at')->nullable(); // vazio = ainda a vencer
            $table->timestamps();

            $table->index(['salon_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
