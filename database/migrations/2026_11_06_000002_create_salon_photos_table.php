<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fotos da página pública do salão (logo, capa e galeria).
     *
     * A imagem fica no próprio banco (em base64), já reduzida pelo navegador antes do envio.
     * Assim as fotos funcionam em qualquer hospedagem, sem configurar um serviço de arquivos.
     */
    public function up(): void
    {
        Schema::create('salon_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 10);
            $table->string('mime_type', 30);
            $table->longText('content');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['salon_id', 'kind']);
        });

        // No Supabase, a tabela nova também fica fechada para a API automática (ver lock_supabase_data_api_out_of_tables)
        if (DB::getDriverName() === 'pgsql' && (int) DB::scalar("select count(*) from pg_roles where rolname in ('anon', 'authenticated')") === 2) {
            DB::statement('ALTER TABLE public.salon_photos ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_photos');
    }
};
