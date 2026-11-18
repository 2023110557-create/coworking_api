<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    // POST /api/reservations
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $user = $request->user();
        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        return DB::transaction(function () use ($request, $user, $start, $end) {
            // Bloquea el espacio para que dos peticiones simultáneas no reserven el mismo horario
            Space::whereKey($request->space_id)->lockForUpdate()->first();

            // Regla 1: no puede cruzarse con otra reserva pending o confirmed del mismo espacio
            $overlaps = Reservation::where('space_id', $request->space_id)
                ->blocking()
                ->overlapping($start, $end)
                ->exists();

            if ($overlaps) {
                return $this->errorResponse('El espacio ya está reservado en ese horario', 409);
            }

            // Regla 2: un cliente no puede tener más de 2 reservas pending/confirmed a futuro
            if ($user->hasRole('client') && $user->reservations()->blocking()->upcoming()->count() >= 2) {
                return $this->errorResponse(
                    'No puedes tener más de 2 reservas pendientes o confirmadas a futuro',
                    422
                );
            }

            $reservation = Reservation::create([
                'user_id' => $user->id,
                'space_id' => $request->space_id,
                'start_time' => $start,
                'end_time' => $end,
                'status' => Reservation::STATUS_PENDING,
                'notes' => $request->notes,
            ]);

            return $this->successResponse(
                $reservation->load('space.spaceType'),
                'Reserva creada correctamente',
                201
            );
        });
    }

    // GET /api/reservations  (historial del usuario autenticado, filtro opcional ?status=)
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,cancelled,completed'],
        ], [
            'status.in' => 'El estado debe ser pending, confirmed, cancelled o completed',
        ]);

        $reservations = $request->user()
            ->reservations()
            ->with('space.spaceType')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('start_time')
            ->get();

        return $this->successResponse($reservations, 'Historial de reservas obtenido correctamente');
    }

    // GET /api/reservations/pending  (staff / admin)
    public function pending(): JsonResponse
    {
        $reservations = Reservation::pending()
            ->with(['user:id,name,email', 'space.spaceType'])
            ->orderBy('start_time')
            ->get();

        return $this->successResponse($reservations, 'Reservas pendientes obtenidas correctamente');
    }

    // PATCH /api/reservations/{reservation}/cancel  (el cliente, solo la propia)
    public function cancel(Request $request, Reservation $reservation): JsonResponse
    {
        if ($reservation->user_id !== $request->user()->id) {
            return $this->errorResponse('Solo puedes cancelar tus propias reservas', 403);
        }

        if (! in_array($reservation->status, [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])) {
            return $this->errorResponse('Solo se pueden cancelar reservas pendientes o confirmadas', 409);
        }

        if ($reservation->start_time->isPast()) {
            return $this->errorResponse('No puedes cancelar una reserva que ya inició', 422);
        }

        $reservation->update(['status' => Reservation::STATUS_CANCELLED]);

        return $this->successResponse($reservation->load('space.spaceType'), 'Reserva cancelada correctamente');
    }

    // PATCH /api/reservations/{reservation}/approve
    public function approve(Reservation $reservation): JsonResponse
    {
        return $this->changeStatus(
            $reservation,
            Reservation::STATUS_PENDING,
            Reservation::STATUS_CONFIRMED,
            'Reserva confirmada correctamente',
            'Solo se pueden confirmar reservas pendientes'
        );
    }

    // PATCH /api/reservations/{reservation}/reject  (queda como cancelled)
    public function reject(Reservation $reservation): JsonResponse
    {
        return $this->changeStatus(
            $reservation,
            Reservation::STATUS_PENDING,
            Reservation::STATUS_CANCELLED,
            'Reserva rechazada correctamente',
            'Solo se pueden rechazar reservas pendientes'
        );
    }

    // PATCH /api/reservations/{reservation}/complete
    public function complete(Reservation $reservation): JsonResponse
    {
        return $this->changeStatus(
            $reservation,
            Reservation::STATUS_CONFIRMED,
            Reservation::STATUS_COMPLETED,
            'Reserva completada correctamente',
            'Solo se pueden completar reservas confirmadas'
        );
    }

    // Cambia el estado solo si la reserva está en el estado esperado
    private function changeStatus(
        Reservation $reservation,
        string $from,
        string $to,
        string $successMessage,
        string $conflictMessage
    ): JsonResponse {
        if ($reservation->status !== $from) {
            return $this->errorResponse($conflictMessage, 409);
        }

        $reservation->update(['status' => $to]);

        return $this->successResponse(
            $reservation->load(['user:id,name,email', 'space.spaceType']),
            $successMessage
        );
    }
}
