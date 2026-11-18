<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceTypeRequest;
use App\Http\Requests\UpdateSpaceTypeRequest;
use App\Models\SpaceType;
use Illuminate\Http\JsonResponse;

class SpaceTypeController extends Controller
{
    // GET /api/space-types
    public function index(): JsonResponse
    {
        $types = SpaceType::withCount('spaces')->orderBy('name')->get();

        return $this->successResponse($types, 'Tipos de espacio obtenidos correctamente');
    }

    // GET /api/space-types/{spaceType}
    public function show(SpaceType $spaceType): JsonResponse
    {
        return $this->successResponse(
            $spaceType->loadCount('spaces'),
            'Tipo de espacio obtenido correctamente'
        );
    }

    // POST /api/space-types
    public function store(StoreSpaceTypeRequest $request): JsonResponse
    {
        $spaceType = SpaceType::create($request->validated());

        return $this->successResponse($spaceType, 'Tipo de espacio creado correctamente', 201);
    }

    // PUT /api/space-types/{spaceType}
    public function update(UpdateSpaceTypeRequest $request, SpaceType $spaceType): JsonResponse
    {
        $spaceType->update($request->validated());

        return $this->successResponse($spaceType, 'Tipo de espacio actualizado correctamente');
    }

    // DELETE /api/space-types/{spaceType}
    public function destroy(SpaceType $spaceType): JsonResponse
    {
        if ($spaceType->spaces()->exists()) {
            return $this->errorResponse(
                'No se puede eliminar un tipo de espacio que tiene espacios asociados',
                409
            );
        }

        $spaceType->delete();

        return $this->successResponse(null, 'Tipo de espacio eliminado correctamente');
    }
}
