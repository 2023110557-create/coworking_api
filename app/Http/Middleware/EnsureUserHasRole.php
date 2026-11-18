<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    use ApiResponser;

    // Uso: role:admin  o  role:admin,staff (alcanza con tener uno)
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('No autenticado. Envía un token válido en el header Authorization', 401);
        }

        if (! $user->hasRole($roles)) {
            return $this->errorResponse('No tienes el rol necesario para realizar esta acción', 403);
        }

        return $next($request);
    }
}
