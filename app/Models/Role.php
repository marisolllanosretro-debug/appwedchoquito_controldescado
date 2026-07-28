<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const MODULOS = ['clientes', 'compras', 'proveedores', 'productos', 'categorias', 'usuarios', 'roles'];

    public const NIVELES = ['ninguno', 'ver', 'gestionar'];

    protected $fillable = ['nombre', 'descripcion', 'permisos'];

    protected function casts(): array
    {
        return [
            'permisos' => 'array',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_rol', 'id');
    }

    public function nivelPara(string $modulo): string
    {
        return is_array($this->permisos) && isset($this->permisos[$modulo])
            ? $this->permisos[$modulo]
            : 'ninguno';
    }

    public function tienePermiso(string $modulo, string $nivelMinimo = 'ver'): bool
    {
        $niveles = array_flip(self::NIVELES);
        $actual = $niveles[$this->nivelPara($modulo)] ?? 0;
        $requerido = $niveles[$nivelMinimo] ?? 0;

        return $actual >= $requerido;
    }

    public function puedeVer(string $modulo): bool
    {
        return $this->tienePermiso($modulo, 'ver');
    }

    public function puedeGestionar(string $modulo): bool
    {
        return $this->tienePermiso($modulo, 'gestionar');
    }
}
