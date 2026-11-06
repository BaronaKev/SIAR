<?php

declare(strict_types=1);

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación compartida para crear y editar categorías (RF010).
 */
class CategoriaRequest extends FormRequest
{
    /** La autorización la hace CategoriaPolicy desde el controlador. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre'      => ['required', 'string', 'max:100', Rule::unique('categorias', 'nombre')->ignore($this->route('categoria'))],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría registrada con este nombre.',
            'nombre.max'      => 'El nombre no puede exceder 100 caracteres.',
        ];
    }
}
