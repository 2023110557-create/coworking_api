<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OccupancyController extends Controller
{
    // GET /api/occupancy?date=2026-12-10  (staff / admin)
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ], [
            'date.required' => 'La fecha es obligatoria',
            'date.date_format' => 'La fecha debe tener el formato AAAA-MM-DD',
        ]);

        $start = Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay();
        $end = $start->copy()->endOfDay();

        $spaces = Space::active()
            ->with([
                'spaceType',
                'reservations' => fn ($q) => $q
                    ->where('status', '!=', 'cancelled')
                    ->overlapping($start, $end)
                    ->with('user:id,name,email')
                    ->orderBy('start_time'),
            ])
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        return $this->successResponse([
            'date' => $request->date,
            'spaces' => $spaces,
        ], 'Ocupación del día obtenida correctamente');
    }
}
