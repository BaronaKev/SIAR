<?php

declare(strict_types=1);

namespace App\Http\Requests\Menu;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación compartida para crear y editar productos (RF007 / RF008).
 */
class ProductoRequest extends FormRequest
{
    /** La autorización la hace ProductoPolicy desde el controlador. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'categoria_id' => ['required', 'exists:categorias,id'],
            'nombre'       => ['required', 'string', 'max:150'],
            'descripcion'  => ['nullable', 'string', 'max:1000'],
            'precio'       => ['required', 'numeric', 'min:0'],
            'stock'        => ['required', 'integer', 'min:0'],
            'estado'       => ['nullable', Rule::in([Producto::DISPONIBLE, Producto::AGOTADO])],
            'imagen'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'categoria_id.required' => 'Debe seleccionar una categoría para el producto.',
            'categoria_id.exists'   => 'La categoría seleccionada no existe.',
            'nombre.required'       => 'El nombre del producto es obligatorio.',
            'precio.required'       => 'El precio es obligatorio.',
            'precio.numeric'        => 'El precio debe ser un valor numérico.',
            'precio.min'            => 'El precio no puede ser negativo.',
            'stock.required'        => 'El stock es obligatorio.',
            'stock.integer'         => 'El stock debe ser un número entero.',
            'stock.min'             => 'El stock no puede ser negativo.',
            'imagen.image'          => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.max'            => 'La imagen no debe pesar más de 2MB.',
        ];
    }

    /**
     * Datos listos para guardar (sin el archivo de imagen).
     * El estado Agotado por stock 0 lo fuerza el modelo Producto (RF011).
     *
     * @return array<string, mixed>
     */
    public function datosProducto(): array
    {
        $datos = $this->safe()->except('imagen');
        $datos['estado'] ??= Producto::DISPONIBLE;

        return $datos;
    }
}
