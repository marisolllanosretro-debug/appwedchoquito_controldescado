<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'apellido_paterno', 'apellido_materno', 'email', 'telefono', 'fecha_ingreso', 'password', 'activo', 'id_rol'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public const ROL_ADMINISTRADOR = 1;

    public const ROL_CAJERA = 2;

    public const ROL_OPERADOR = 3;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'activo' => 'boolean',
            'fecha_ingreso' => 'date',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_rol', 'id');
    }

    public function isActivo(): bool
    {
        return $this->activo ?? true;
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function hasRol(int ...$roles): bool
    {
        return $this->id_rol !== null
            && in_array((int) $this->id_rol, $roles, true);
    }

    public function esAdministrador(): bool
    {
        return $this->hasRol(self::ROL_ADMINISTRADOR);
    }

    public function esCajera(): bool
    {
        return $this->hasRol(self::ROL_CAJERA);
    }

    public function esOperador(): bool
    {
        return $this->hasRol(self::ROL_OPERADOR);
    }
}
