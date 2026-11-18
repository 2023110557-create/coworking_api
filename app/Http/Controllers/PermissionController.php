<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    // GET /api/permissions
    public function index(): JsonResponse
    {
        return $this->successResponse(
            Permission::orderBy('name')->get(['id', 'name', 'label']),
            'Permisos obtenidos correctamente'
        );
    }
}
