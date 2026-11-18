<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

// Convierte cualquier excepción de la API en una respuesta con el formato del trait
class ApiExceptionHandler
{
    use ApiResponser;

    public function handle(Throwable $e): JsonResponse
    {
        // 422: falló la validación, el detalle va en "errors"
        if ($e instanceof ValidationException) {
            return $this->errorResponse('Los datos enviados no son válidos', 422, $e->errors());
        }

        // 401: no se envió token o no es válido
        if ($e instanceof AuthenticationException) {
            return $this->errorResponse('No autenticado. Envía un token válido en el header Authorization', 401);
        }

        // 403, 404, 405, 429...: Laravel convierte también el modelo no encontrado en un 404
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            $messages = [
                403 => 'No tienes permiso para realizar esta acción',
                404 => 'El recurso solicitado no existe',
                405 => 'Método HTTP no permitido para esta ruta',
                429 => 'Demasiadas solicitudes, intenta de nuevo más tarde',
            ];

            return $this->errorResponse($messages[$status] ?? ($e->getMessage() ?: 'Error en la solicitud'), $status);
        }

        // 500: cualquier otro fallo. Con APP_DEBUG=true se ve el mensaje real, si no uno genérico
        $message = config('app.debug') ? $e->getMessage() : 'Error interno del servidor';

        return $this->errorResponse($message, 500);
    }
}
