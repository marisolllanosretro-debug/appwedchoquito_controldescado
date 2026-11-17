<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoPrecioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->esAdministrador()
            || ($user?->rol?->puedeGestionar('productos') ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'precio_venta' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
        ];
    }
}
