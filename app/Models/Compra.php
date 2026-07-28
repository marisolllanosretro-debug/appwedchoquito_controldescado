<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_proveedor',
        'id_producto',
        'proveedor',
        'producto',
        'cantidad',
        'precio_unitario',
        'total',
        'fecha_compra',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'total' => 'decimal:2',
            'fecha_compra' => 'date',
            'cantidad' => 'integer',
        ];
    }

    public function proveedorRel(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function productoRel(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
