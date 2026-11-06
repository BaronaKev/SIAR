<?php

declare(strict_types=1);

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUsuarioRequest extends FormRequest
{
    /** La autorización la hace UserPolicy desde el controlador. */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['estado' => $this->boolean('estado')]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->route('usuario')),
            ],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role'     => ['required', 'exists:roles,name'],
            'estado'   => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'      => 'El nombre completo es obligatorio.',
            'email.required'     => 'El correo electrónico es obligatorio.',
            'email.unique'       => 'Este correo ya pertenece a otro usuario.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres si desea cambiarla.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'role.required'      => 'Debe asignar un rol al usuario.',
        ];
    }
}
