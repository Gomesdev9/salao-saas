<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('price', 10, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Quais serviços cada profissional faz
        Schema::create('professional_service', function (Blueprint $table) {
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['professional_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_service');
        Schema::dropIfExists('services');
    }
};
