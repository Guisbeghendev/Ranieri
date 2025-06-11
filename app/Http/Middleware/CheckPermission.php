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
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $permissionType
     * @param  string $permissionName
     * @param  string|null $modelParameter
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
            if (is_object($resolvedParameter)) {
                $modelInstance = $resolvedParameter;
            }
        }

        try {
            $checker = $this->resolvePermissionChecker($permissionType);

            if (! $checker($permissionName, $user, $modelInstance)) {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }

        } catch (InvalidArgumentException $e) {
            abort(500, 'Configuração de permissão inválida: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Resolve the permission checker closure.
     *
     * @param string $type
     * @return \Closure
     * @throws InvalidArgumentException
     */
    protected function resolvePermissionChecker(string $type): Closure
    {
        return match ($type) {
            'gate' => function (string $permissionName, $user, ?object $modelInstance) {
                // CORREÇÃO: Sempre passar o modelo explicitamente para o Gate::allows
                return Gate::allows($permissionName, $modelInstance);
            },
            'policy' => function (string $permissionName, $user, ?object $modelInstance) {
                return Gate::forUser($user)->allows($permissionName, $modelInstance);
            },
            default => throw new InvalidArgumentException("Tipo de permissão '$type' não suportado."),
        };
    }
}
