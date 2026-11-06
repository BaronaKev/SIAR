<?php

declare(strict_types=1);

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CU017: mesa + productos con su cantidad. Formato: productos[ID_PRODUCTO] = cantidad.
 */
class StorePedidoRequest extends FormRequest
{
    /** La autorización la hace PedidoPolicy desde el controlador. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'mesa_id'       => ['required', 'exists:mesas,id'],
            'productos'     => ['required', 'array'],
            'productos.*'   => ['nullable', 'integer', 'min:0', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'mesa_id.required'   => 'Debe asignar el pedido a una mesa.',
            'mesa_id.exists'     => 'La mesa seleccionada no existe.',
            'productos.required' => 'El pedido debe tener al menos un producto.',
            'productos.*.min'    => 'Las cantidades no pueden ser negativas.',
        ];
    }

    /** @return array<int, int> [producto_id => cantidad] solo con cantidades mayores a 0 */
    public function productos(): array
    {
        return collect($this->validated('productos'))
            ->map(fn ($cantidad) => (int) $cantidad)
            ->filter(fn (int $cantidad) => $cantidad > 0)
            ->all();
    }
}
