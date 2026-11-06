<?php

declare(strict_types=1);

namespace App\Http\Requests\Facturacion;

use App\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU026: método de pago y monto. En efectivo se pide el dinero recibido para calcular las vueltas. */
class PagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'metodo'         => ['required', Rule::enum(MetodoPago::class)],
            'monto'          => ['required', 'numeric', 'gt:0'],
            'monto_recibido' => [
                'nullable', 'numeric', 'gte:monto',
                Rule::requiredIf($this->input('metodo') === MetodoPago::Efectivo->value),
            ],
            'referencia'     => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'metodo.required'         => 'Seleccione el método de pago.',
            'monto.gt'                => 'El monto debe ser mayor a 0.',
            'monto_recibido.required' => 'Para pagos en efectivo ingrese el dinero recibido.',
            'monto_recibido.gte'      => 'El dinero recibido debe cubrir el monto a pagar.',
        ];
    }
}
