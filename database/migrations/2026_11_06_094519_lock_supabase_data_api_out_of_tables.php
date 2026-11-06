<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * No Supabase, toda tabela do schema "public" também fica aberta na API automática dele
     * (com a chave "anon", que é pública). O sistema só fala com o banco pelo Laravel, então
     * essa porta é fechada: os papéis anon e authenticated perdem o acesso às tabelas de hoje
     * e às que forem criadas depois, e a segurança por linha (RLS) fica ligada sem nenhuma regra,
     * o que bloqueia tudo pela API. O Laravel entra como dono das tabelas e não é afetado.
     *
     * Só roda no Postgres do Supabase (onde existem os papéis anon e authenticated).
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! $this->hasSupabaseApiRoles()) {
            return;
        }

        DB::unprepared(<<<'SQL'
            REVOKE ALL ON ALL TABLES IN SCHEMA public FROM anon, authenticated;
            REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM anon, authenticated;
            REVOKE ALL ON ALL FUNCTIONS IN SCHEMA public FROM anon, authenticated;
            ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON TABLES FROM anon, authenticated;
            ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON SEQUENCES FROM anon, authenticated;
            ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON FUNCTIONS FROM anon, authenticated;
            SQL);

        foreach (DB::select("select tablename from pg_tables where schemaname = 'public'") as $table) {
            DB::statement(sprintf('ALTER TABLE public.%s ENABLE ROW LEVEL SECURITY', $this->quoteIdentifier($table->tablename)));
        }
    }

    private function hasSupabaseApiRoles(): bool
    {
        return (int) DB::scalar("select count(*) from pg_roles where rolname in ('anon', 'authenticated')") === 2;
    }

    private function quoteIdentifier(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }
};
