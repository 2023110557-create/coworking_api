<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('spaces', 'name')->ignore($this->route('space')),
            ],
            'space_type_id' => ['sometimes', 'required', 'integer', 'exists:space_types,id'],
            'floor' => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000'],
            'price_per_hour' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.max' => 'El nombre no puede superar los 255 caracteres',
            'name.unique' => 'Ya existe un espacio con ese nombre',
            'space_type_id.required' => 'El tipo de espacio es obligatorio',
            'space_type_id.integer' => 'El tipo de espacio no es válido',
            'space_type_id.exists' => 'El tipo de espacio seleccionado no existe',
            'floor.required' => 'El piso es obligatorio',
            'floor.integer' => 'El piso debe ser un número entero',
            'floor.min' => 'El piso no puede ser negativo',
            'floor.max' => 'El piso no puede ser mayor a 100',
            'capacity.required' => 'La capacidad es obligatoria',
            'capacity.integer' => 'La capacidad debe ser un número entero',
            'capacity.min' => 'La capacidad debe ser de al menos 1 persona',
            'capacity.max' => 'La capacidad no puede superar las 1000 personas',
            'price_per_hour.required' => 'El precio por hora es obligatorio',
            'price_per_hour.numeric' => 'El precio por hora debe ser un número',
            'price_per_hour.min' => 'El precio por hora no puede ser negativo',
            'price_per_hour.max' => 'El precio por hora es demasiado alto',
            'is_active.boolean' => 'El campo activo debe ser verdadero o falso',
        ];
    }
}
