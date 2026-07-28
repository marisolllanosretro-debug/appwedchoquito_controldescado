<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rol = $this->route('rol');
        $rolId = $rol instanceof Role ? $rol->id : null;

        return [
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'nombre')->ignore($rolId, 'id'),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['required', 'string', Rule::in(Role::NIVELES)],
        ];
    }

    public function attributes(): array
    {
        return [
            'permisos.clientes' => 'permiso de clientes',
            'permisos.compras' => 'permiso de compras',
            'permisos.usuarios' => 'permiso de usuarios',
            'permisos.roles' => 'permiso de roles',
        ];
    }
}
