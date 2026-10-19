<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('whatsapp')->nullable();
            $table->date('birthday')->nullable();
            $table->text('notes')->nullable(); // alergias, preferências etc.
            $table->timestamps();

            $table->index(['salon_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
