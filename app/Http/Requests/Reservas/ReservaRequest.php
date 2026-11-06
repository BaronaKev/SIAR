<?php

declare(strict_types=1);

namespace App\Http\Requests\Reservas;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CU015: datos de la reserva. Al editar no se cambia la mesa (se cancela y se crea otra).
 */
class ReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $creando = $this->isMethod('post');

        return [
            'mesa_id'        => [$creando ? 'required' : 'prohibited', 'exists:mesas,id'],
            'nombre_cliente' => ['required', 'string', 'max:150'],
            'fecha'          => ['required', 'date', 'after_or_equal:today'],
            'hora'           => ['required', 'date_format:H:i'],
            'personas'       => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'mesa_id.required'        => 'Seleccione la mesa a reservar.',
            'nombre_cliente.required' => 'El nombre del cliente es obligatorio.',
            'fecha.after_or_equal'    => 'La fecha de la reserva no puede ser anterior a hoy.',
            'hora.date_format'        => 'La hora debe tener el formato HH:MM.',
        ];
    }
}
