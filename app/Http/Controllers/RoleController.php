<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private const PROTECTED = ['admin', 'staff', 'client'];

    private const MESSAGES = [
        'name.required' => 'El nombre del rol es obligatorio',
        'name.max' => 'El nombre no puede superar los 50 caracteres',
        'name.unique' => 'Ya existe un rol con ese nombre',
        'name.alpha_dash' => 'El nombre solo puede tener letras, números, guiones y guiones bajos',
        'label.required' => 'La etiqueta del rol es obligatoria',
        'label.max' => 'La etiqueta no puede superar los 100 caracteres',
        'permissions.array' => 'Los permisos deben enviarse como una lista de ids',
        'permissions.*.exists' => 'Uno de los permisos seleccionados no existe',
    ];

    // GET /api/roles
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions:permissions.id,permissions.name,permissions.label')
            ->withCount('users')
            ->orderBy('id')
            ->get();

        return $this->successResponse($roles, 'Roles obtenidos correctamente');
    }

    // GET /api/roles/{role}
    public function show(Role $role): JsonResponse
    {
        return $this->successResponse(
            $role->load('permissions:permissions.id,permissions.name,permissions.label')->loadCount('users'),
            'Rol obtenido correctamente'
        );
    }

    // POST /api/roles
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:roles,name'],
            'label' => ['required', 'string', 'max:100'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ], self::MESSAGES);

        $role = Role::create(['name' => $data['name'], 'label' => $data['label']]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return $this->successResponse(
            $role->load('permissions:permissions.id,permissions.name,permissions.label'),
            'Rol creado correctamente',
            201
        );
    }

    // PUT /api/roles/{role}  (label y/o permisos)
    public function update(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:100'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ], self::MESSAGES);

        if ($role->name === 'admin' && isset($data['permissions'])) {
            return $this->errorResponse('No se pueden modificar los permisos del rol admin', 409);
        }

        if (isset($data['label'])) {
            $role->update(['label' => $data['label']]);
        }

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return $this->successResponse(
            $role->load('permissions:permissions.id,permissions.name,permissions.label'),
            'Rol actualizado correctamente'
        );
    }

    // DELETE /api/roles/{role}
    public function destroy(Role $role): JsonResponse
    {
        if (in_array($role->name, self::PROTECTED)) {
            return $this->errorResponse('No se pueden eliminar los roles base del sistema', 409);
        }

        if ($role->users()->exists()) {
            return $this->errorResponse('No se puede eliminar un rol que tiene usuarios asignados', 409);
        }

        $role->permissions()->detach();
        $role->delete();

        return $this->successResponse(null, 'Rol eliminado correctamente');
    }
}
