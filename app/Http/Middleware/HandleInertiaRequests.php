<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Liga o Laravel às telas em React (Inertia): escolhe o HTML base e manda os dados que toda tela usa.
 */
class HandleInertiaRequests extends Middleware
{
    /** HTML base carregado na primeira visita (resources/views/app.blade.php). */
    protected $rootView = 'app';

    /**
     * Dados compartilhados com todas as telas: quem está logado, a empresa e o aviso de sucesso.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'auth' => fn () => $this->auth($request),
            'flash' => fn () => ['status' => $request->session()->get('status')],
        ];
    }

    private function auth(Request $request): ?array
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $salon = $user->salon;

        return [
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'salon' => [
                'name' => $salon->name,
                'plan' => $salon->plan->label(),
                'onTrial' => $salon->onTrial(),
                'trialDaysLeft' => $salon->trialDaysLeft(),
            ],
        ];
    }
}
