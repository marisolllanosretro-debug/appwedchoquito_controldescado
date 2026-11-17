<?php

namespace App\Models;

use Database\Factories\PedidoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    /** @use HasFactory<PedidoFactory> */
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'user_id',
        'numero',
        'cliente_nombre',
        'cliente_email',
        'cliente_telefono',
        'direccion',
        'subtotal',
        'total',
        'metodo_pago',
        'estado',
        'completado_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'completado_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function getRouteKeyName(): string
    {
        return 'numero';
    }
}
