<?php

declare(strict_types=1);

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

/** CU018: agregar un producto a un pedido existente. */
class AgregarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'exists:productos,id'],
            'cantidad'    => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'producto_id.required' => 'Seleccione un producto.',
            'cantidad.min'         => 'La cantidad debe ser al menos 1.',
        ];
    }
}
