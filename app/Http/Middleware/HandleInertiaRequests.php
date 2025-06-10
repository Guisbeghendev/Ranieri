<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
// use Tightenco\Ziggy\Ziggy; // COMENTADO/REMOVIDO: Esta linha estava causando o erro "Classe não encontrada"

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared to all Inertia requests.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ?
                    // Carrega as relações 'profile' e 'avatarRelation' para o usuário logado
                    $request->user()->load(['profile.avatarRelation'])->toArray()
                    : null,
            ],
            // AQUI ESTAVA A REFERÊNCIA AO ZIGGY QUE ESTAVA CAUSANDO O ERRO.
            // Ela foi removida, pois a classe não foi encontrada.
            // Se você instalar o Ziggy no futuro (composer require tightenco/ziggy),
            // você poderá adicionar isso de volta, se necessário.
            // 'ziggy' => fn () => [
            //     ...(new Ziggy)->toArray(),
            //     'location' => $request->url(),
            // ],
        ]);
    }
}
