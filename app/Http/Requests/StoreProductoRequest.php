<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductoRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255'],
            'id_categoria' => [
                'required',
                'integer',
                Rule::exists('categorias', 'id')->where('activo', true),
            ],
            'categoria' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_compra' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'precio_venta' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999999'],
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:999999999'],
            'unidad_medida' => [
                'required',
                'string',
                Rule::in([
                    'Unidad',
                    'Kilogramo',
                    'Libra',
                    'Litro',
                    'Quintal',
                    'Arroba',
                    'Caja',
                    'Bolsa',
                    'Saco',
                    'Paquete',
                    'Docena',
                ]),
            ],
        ];
    }
}
