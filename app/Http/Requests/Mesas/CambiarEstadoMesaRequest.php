<?php

declare(strict_types=1);

namespace App\Http\Requests\Mesas;

use App\Enums\EstadoMesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoMesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(EstadoMesa::valores())],
        ];
    }
}
