<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('space_types', 'name')->ignore($this->route('spaceType')),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del tipo de espacio es obligatorio',
            'name.max' => 'El nombre no puede superar los 100 caracteres',
            'name.unique' => 'Ya existe un tipo de espacio con ese nombre',
            'description.max' => 'La descripción no puede superar los 500 caracteres',
        ];
    }
}
