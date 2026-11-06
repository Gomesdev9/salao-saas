<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Personalização da página pública do salão: cores, frase de destaque, texto "Sobre" e contatos.
     */
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->string('page_accent_color', 7)->nullable()->after('pix_key');
            $table->string('page_background_color', 7)->nullable()->after('page_accent_color');
            $table->string('page_headline', 120)->nullable()->after('page_background_color');
            $table->text('page_about')->nullable()->after('page_headline');
            $table->string('address')->nullable()->after('page_about');
            $table->string('instagram', 60)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn(['page_accent_color', 'page_background_color', 'page_headline', 'page_about', 'address', 'instagram']);
        });
    }
};
