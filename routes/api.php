<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\SpaceTypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OccupancyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

// Rutas públicas
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Rutas protegidas con token de Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Catálogo (cliente, staff y admin)
    Route::middleware('permission:spaces.view')->group(function () {
        Route::get('spaces', [SpaceController::class, 'index']);
        Route::get('spaces/{space}', [SpaceController::class, 'show']);
        Route::get('space-types', [SpaceTypeController::class, 'index']);
    });

    // Administración de espacios
    Route::post('spaces', [SpaceController::class, 'store'])->middleware('permission:spaces.create');
    Route::put('spaces/{space}', [SpaceController::class, 'update'])->middleware('permission:spaces.update');
    Route::delete('spaces/{space}', [SpaceController::class, 'destroy'])->middleware('permission:spaces.delete');

    // Administración de tipos de espacio
    Route::get('space-types/{spaceType}', [SpaceTypeController::class, 'show'])->middleware('permission:space_types.view');
    Route::post('space-types', [SpaceTypeController::class, 'store'])->middleware('permission:space_types.create');
    Route::put('space-types/{spaceType}', [SpaceTypeController::class, 'update'])->middleware('permission:space_types.update');
    Route::delete('space-types/{spaceType}', [SpaceTypeController::class, 'destroy'])->middleware('permission:space_types.delete');

    // Reservas: staff / admin
    Route::get('reservations/pending', [ReservationController::class, 'pending'])->middleware('permission:reservations.view_pending');
    Route::patch('reservations/{reservation}/approve', [ReservationController::class, 'approve'])->middleware('permission:reservations.approve');
    Route::patch('reservations/{reservation}/reject', [ReservationController::class, 'reject'])->middleware('permission:reservations.reject');
    Route::patch('reservations/{reservation}/complete', [ReservationController::class, 'complete'])->middleware('permission:reservations.complete');

    // Reservas: cliente
    Route::get('reservations', [ReservationController::class, 'index'])->middleware('permission:reservations.view_own');
    Route::post('reservations', [ReservationController::class, 'store'])->middleware('permission:reservations.create');
    Route::patch('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->middleware('permission:reservations.cancel_own');
});
