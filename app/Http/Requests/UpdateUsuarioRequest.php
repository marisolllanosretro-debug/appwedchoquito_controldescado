<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'telefono' => ['nullable', 'string', 'max:20'],
            'fecha_ingreso' => ['nullable', 'date'],
            'password' => ['nullable', 'string', Password::default()],
            'id_rol' => ['required', 'integer', 'exists:roles,id'],
            'activo' => ['boolean'],
        ];
    }
}
