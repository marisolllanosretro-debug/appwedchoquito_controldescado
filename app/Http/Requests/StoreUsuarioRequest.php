<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:users,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'fecha_ingreso' => ['nullable', 'date'],
            'password' => ['required', 'string', 'min:8'],
            'id_rol' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}
