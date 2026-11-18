<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    use ApiResponser;

    // Uso: permission:reservations.approve
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('No autenticado. Envía un token válido en el header Authorization', 401);
        }

        if (! $user->hasPermission($permission)) {
            return $this->errorResponse('No tienes permiso para realizar esta acción', 403);
        }

        return $next($request);
    }
}
