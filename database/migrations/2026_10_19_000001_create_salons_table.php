<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada salão é um "inquilino" (tenant): todos os outros dados apontam para ele.
     */
    public function up(): void
    {
        Schema::create('salons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // usado no link público de agendamento
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('salon_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('owner')->after('password'); // owner, reception, professional
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('salon_id');
            $table->dropColumn('role');
        });

        Schema::dropIfExists('salons');
    }
};
