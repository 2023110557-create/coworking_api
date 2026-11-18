<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceRequest;
use App\Http\Requests\UpdateSpaceRequest;
use App\Models\Space;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    // GET /api/spaces  (catálogo con filtros)
    // Filtros: space_type_id, min_capacity, start_time + end_time (solo espacios libres en ese rango)
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'space_type_id' => ['nullable', 'integer', 'exists:space_types,id'],
            'min_capacity' => ['nullable', 'integer', 'min:1'],
            'start_time' => ['nullable', 'date', 'required_with:end_time'],
            'end_time' => ['nullable', 'date', 'required_with:start_time', 'after:start_time'],
        ], [
            'space_type_id.integer' => 'El tipo de espacio no es válido',
            'space_type_id.exists' => 'El tipo de espacio seleccionado no existe',
            'min_capacity.integer' => 'La capacidad debe ser un número entero',
            'min_capacity.min' => 'La capacidad debe ser de al menos 1 persona',
            'start_time.date' => 'La fecha y hora de inicio no son válidas',
            'start_time.required_with' => 'Debes enviar también la fecha y hora de inicio',
            'end_time.date' => 'La fecha y hora de fin no son válidas',
            'end_time.required_with' => 'Debes enviar también la fecha y hora de fin',
            'end_time.after' => 'La hora de fin debe ser posterior a la de inicio',
        ]);

        $query = Space::with('spaceType');

        // Solo quien puede editar espacios ve también los inactivos (con ?all=1)
        $seeAll = $request->boolean('all') && $request->user()->hasPermission('spaces.update');

        if (! $seeAll) {
            $query->active();
        }

        $query
            ->when($request->filled('space_type_id'), fn ($q) => $q->ofType((int) $request->space_type_id))
            ->when($request->filled('min_capacity'), fn ($q) => $q->minCapacity((int) $request->min_capacity))
            ->when(
                $request->filled('start_time') && $request->filled('end_time'),
                fn ($q) => $q->availableBetween($request->start_time, $request->end_time)
            );

        $spaces = $query->orderBy('floor')->orderBy('name')->get();

        return $this->successResponse($spaces, 'Espacios obtenidos correctamente');
    }

    // GET /api/spaces/{space}
    public function show(Request $request, Space $space): JsonResponse
    {
        // Un espacio inactivo solo lo ve quien puede editarlos; para los demás "no existe"
        if (! $space->is_active && ! $request->user()->hasPermission('spaces.update')) {
            abort(404);
        }

        return $this->successResponse($space->load('spaceType'), 'Espacio obtenido correctamente');
    }

    // POST /api/spaces
    public function store(StoreSpaceRequest $request): JsonResponse
    {
        $space = Space::create($request->validated());

        return $this->successResponse($space->load('spaceType'), 'Espacio creado correctamente', 201);
    }

    // PUT /api/spaces/{space}
    public function update(UpdateSpaceRequest $request, Space $space): JsonResponse
    {
        $space->update($request->validated());

        return $this->successResponse($space->load('spaceType'), 'Espacio actualizado correctamente');
    }

    // DELETE /api/spaces/{space}
    public function destroy(Space $space): JsonResponse
    {
        // Las reservas se borrarían en cascada, así que mejor no permitirlo
        if ($space->reservations()->exists()) {
            return $this->errorResponse(
                'No se puede eliminar un espacio que tiene reservas. Desactívalo con is_active = false',
                409
            );
        }

        $space->delete();

        return $this->successResponse(null, 'Espacio eliminado correctamente');
    }
}
