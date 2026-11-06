<?php

declare(strict_types=1);

namespace App\Http\Requests\Facturacion;

use App\Services\Descuentos\DescuentoFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU025: tipo (porcentaje o valor fijo) y valor del descuento. */
class DescuentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tipo'  => ['required', Rule::in(DescuentoFactory::tipos())],
            'valor' => [
                'required', 'numeric', 'min:0',
                Rule::when($this->input('tipo') === DescuentoFactory::PORCENTAJE, ['max:100']),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tipo.required'  => 'Seleccione el tipo de descuento.',
            'valor.required' => 'Ingrese el valor del descuento.',
            'valor.max'      => 'El porcentaje no puede ser mayor a 100.',
        ];
    }
}
