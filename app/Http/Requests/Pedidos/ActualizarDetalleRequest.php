<?php

declare(strict_types=1);

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

/** CU019: cambiar la cantidad de un producto del pedido. */
class ActualizarDetalleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cantidad' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
