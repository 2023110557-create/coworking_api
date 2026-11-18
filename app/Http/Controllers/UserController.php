<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const MESSAGES = [
        'name.required' => 'El nombre es obligatorio',
        'name.max' => 'El nombre no puede superar los 255 caracteres',
        'email.required' => 'El correo es obligatorio',
        'email.email' => 'El correo no tiene un formato válido',
        'email.unique' => 'Este correo ya está registrado',
        'password.required' => 'La contraseña es obligatoria',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        'roles.required' => 'Debes asignar al menos un rol',
        'roles.min' => 'Debes asignar al menos un rol',
        'roles.array' => 'Los roles deben enviarse como una lista de ids',
        'roles.*.exists' => 'Uno de los roles seleccionados no existe',
    ];

    // GET /api/users  (filtro opcional ?role=client)
    public function index(Request $request): JsonResponse
    {
        $users = User::with('roles:roles.id,roles.name,roles.label')
            ->when($request->filled('role'), fn ($q) => $q->whereHas(
                'roles', fn ($r) => $r->where('name', $request->role)
            ))
            ->orderBy('name')
            ->get();

        return $this->successResponse($users, 'Usuarios obtenidos correctamente');
    }

    // GET /api/users/{user}
    public function show(User $user): JsonResponse
    {
        return $this->successResponse(
            $user->load('roles:roles.id,roles.name,roles.label'),
            'Usuario obtenido correctamente'
        );
    }

    // POST /api/users
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ], self::MESSAGES);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // el cast "hashed" la encripta
        ]);
        $user->roles()->sync($data['roles']);

        return $this->successResponse(
            $user->load('roles:roles.id,roles.name,roles.label'),
            'Usuario creado correctamente',
            201
        );
    }

    // PUT /api/users/{user}
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'roles' => ['sometimes', 'required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ], self::MESSAGES);

        $user->update(collect($data)->except('roles')->all());

        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        return $this->successResponse(
            $user->load('roles:roles.id,roles.name,roles.label'),
            'Usuario actualizado correctamente'
        );
    }

    // DELETE /api/users/{user}
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return $this->errorResponse('No puedes eliminar tu propio usuario', 409);
        }

        $user->tokens()->delete();
        $user->roles()->detach();
        $user->delete();

        return $this->successResponse(null, 'Usuario eliminado correctamente');
    }
}
