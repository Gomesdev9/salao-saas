<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Datas por extenso em português ("quarta-feira, 21 de outubro")
        Carbon::setLocale(config('app.locale'));

        // URLs em português: /clientes/novo e /clientes/1/editar
        Route::resourceVerbs([
            'create' => 'novo',
            'edit' => 'editar',
        ]);
    }
}
