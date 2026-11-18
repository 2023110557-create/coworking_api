<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Rutas protegidas con token de Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // ===== PRUEBAS TEMPORALES (borrar en el paso 7) =====
    Route::get('test/admin', fn () => response()->json([
        'success' => true, 'message' => 'Acceso permitido: rol admin', 'data' => null,
    ]))->middleware('role:admin');

    Route::get('test/staff-or-admin', fn () => response()->json([
        'success' => true, 'message' => 'Acceso permitido: rol staff o admin', 'data' => null,
    ]))->middleware('role:admin,staff');

    Route::get('test/approve', fn () => response()->json([
        'success' => true, 'message' => 'Acceso permitido: permiso reservations.approve', 'data' => null,
    ]))->middleware('permission:reservations.approve');

    Route::get('test/create-reservation', fn () => response()->json([
        'success' => true, 'message' => 'Acceso permitido: permiso reservations.create', 'data' => null,
    ]))->middleware('permission:reservations.create');
    // ===== FIN PRUEBAS TEMPORALES =====
});
