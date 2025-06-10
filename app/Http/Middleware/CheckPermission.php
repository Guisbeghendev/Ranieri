<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use InvalidArgumentException;

class CheckPermission
{
    /**
     * Lida com uma requisição HTTP.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $permissionType     // Indica se é uma 'gate' ou 'policy'
     * @param  string $permissionName     // O nome da gate (ex: 'admin-only') ou da ação da policy (ex: 'update')
     * @param  string|null $modelParameter // Opcional: o nome do parâmetro da rota que representa o modelo (ex: 'gallery', 'user')
     */
    public function handle(Request $request, Closure $next, string $permissionType, string $permissionName, ?string $modelParameter = null): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $modelInstance = null;

        if ($modelParameter) {
            $resolvedParameter = $request->route($modelParameter);
            // Garante que $modelInstance é um objeto antes de atribuir.
            // Isso evita o TypeError se o parâmetro da rota não for um objeto (por exemplo, um ID string).
            if (is_object($resolvedParameter)) {
                $modelInstance = $resolvedParameter;
            }
        }

        try {
            // Resolve a estratégia de verificação de permissão
            $checker = $this->resolvePermissionChecker($permissionType);

            // Executa a verificação
            if (! $checker($permissionName, $user, $modelInstance)) {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }

        } catch (InvalidArgumentException $e) {
            abort(500, 'Configuração de permissão inválida: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Retorna uma closure para verificar o tipo de permissão especificado.
     *
     * @param string $type
     * @return \Closure (string $permissionName, \App\Models\User $user, ?object $modelInstance = null)
     * @throws InvalidArgumentException
     */
    protected function resolvePermissionChecker(string $type): Closure
    {
        return match ($type) {
            'gate' => function (string $permissionName, $user, ?object $modelInstance) {
                // *** CORREÇÃO CRÍTICA AQUI ***
                // SEMPRE passa $modelInstance explicitamente como segundo argumento.
                // As Gates DEVERÃO ter seus argumentos de modelo como nullable (ex: ?Profile $profile = null).
                return Gate::allows($permissionName, $modelInstance);
            },
            'policy' => function (string $permissionName, $user, ?object $modelInstance) {
                // Para Policies, o primeiro argumento é o usuário, o segundo é o modelo (se existir)
                // Se o modelInstance não existe (ex: 'create' policy), passa apenas o usuário
                return Gate::forUser($user)->allows($permissionName, $modelInstance);
            },
            default => throw new InvalidArgumentException("Tipo de permissão '$type' não suportado."),
        };
    }
}
