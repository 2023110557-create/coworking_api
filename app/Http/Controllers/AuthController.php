<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/register
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        // Todo usuario que se registra por la API es cliente
        $user->roles()->attach(Role::where('name', 'client')->value('id'));

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $this->userData($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Usuario registrado correctamente', 201);
    }

    // POST /api/login
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Las credenciales son incorrectas', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $this->userData($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Inicio de sesión correcto');
    }

    // POST /api/logout (invalida solo el token con el que se hizo la petición)
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Sesión cerrada correctamente');
    }

    // GET /api/me
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($this->userData($request->user()), 'Usuario autenticado');
    }

    // Usuario con sus roles y la lista de permisos (nombres) de todos sus roles
    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles()->get(['roles.id', 'roles.name', 'roles.label']),
            'permissions' => $user->allPermissions()->pluck('name')->values(),
        ];
    }
}
