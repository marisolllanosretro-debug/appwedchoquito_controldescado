<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cliente_nombre' => ['required', 'string', 'max:255'],
            'cliente_email' => ['required', 'email', 'max:255'],
            'cliente_telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'metodo_pago' => ['required', 'in:efectivo,transferencia'],
            'items' => ['required', 'array', 'list', 'min:1', 'max:50'],
            'items.*' => ['required', 'array:producto_id,cantidad'],
            'items.*.producto_id' => ['required', 'integer', 'distinct:strict', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }
}
