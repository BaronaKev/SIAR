<?php

declare(strict_types=1);

namespace App\Http\Requests\Pedidos;

use App\Enums\EstadoPedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoPedidoRequest extends FormRequest
{
    /** La autorización (transición + permiso) la hace PedidoPolicy::cambiarEstado. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::enum(EstadoPedido::class)],
        ];
    }

    public function destino(): EstadoPedido
    {
        return EstadoPedido::from($this->validated('estado'));
    }
}
