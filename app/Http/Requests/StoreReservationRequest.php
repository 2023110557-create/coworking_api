<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // El espacio debe existir y estar activo
            'space_id' => ['required', 'integer', Rule::exists('spaces', 'id')->where('is_active', true)],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'space_id.required' => 'El espacio es obligatorio',
            'space_id.integer' => 'El espacio no es válido',
            'space_id.exists' => 'El espacio no existe o no está disponible',
            'start_time.required' => 'La fecha y hora de inicio son obligatorias',
            'start_time.date' => 'La fecha y hora de inicio no son válidas',
            'start_time.after' => 'La hora de inicio debe ser posterior a la hora actual',
            'end_time.required' => 'La fecha y hora de fin son obligatorias',
            'end_time.date' => 'La fecha y hora de fin no son válidas',
            'end_time.after' => 'La hora de fin debe ser posterior a la de inicio',
            'notes.max' => 'Las notas no pueden superar los 500 caracteres',
        ];
    }
}
