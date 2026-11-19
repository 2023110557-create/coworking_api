<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Coworking API",
    version: "1.0.0",
    description: "Documentación de la API de Coworking"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Servidor local"
)]
#[OA\Get(
    path: "/api/me",
    summary: "Obtener usuario autenticado",
    responses: [
        new OA\Response(response: 200, description: "OK")
    ]
)]
abstract class Controller
{
    use ApiResponser;
}
