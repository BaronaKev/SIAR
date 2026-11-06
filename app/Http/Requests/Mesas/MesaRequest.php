<?php

declare(strict_types=1);

namespace App\Http\Requests\Mesas;

use App\Enums\EstadoMesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación compartida para crear y editar mesas (RF012 / RF013).
 */
class MesaRequest extends FormRequest
{
    /** La autorización la hace MesaPolicy desde el controlador. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero'    => ['required', 'string', 'max:50', Rule::unique('mesas', 'numero')->ignore($this->route('mesa'))],
            'capacidad' => ['required', 'integer', 'min:1', 'max:50'],
            'ubicacion' => ['nullable', 'string', 'max:100'],
            'estado'    => ['required', Rule::in(EstadoMesa::valores())],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'numero.required'    => 'El número o identificador de mesa es obligatorio.',
            'numero.unique'      => 'Ya existe una mesa con ese número o identificador.',
            'capacidad.required' => 'La capacidad de personas es obligatoria.',
            'capacidad.integer'  => 'La capacidad debe ser un número entero.',
            'capacidad.min'      => 'La capacidad mínima debe ser de al menos 1 persona.',
            'estado.required'    => 'Debe seleccionar un estado.',
            'estado.in'          => 'El estado seleccionado no es válido.',
        ];
    }
}
