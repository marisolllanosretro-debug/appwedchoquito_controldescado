<?php

use App\Http\Controllers\Categorias\CategoriaController;
use App\Http\Controllers\Clientes\ClienteController;
use App\Http\Controllers\Compras\CompraController;
use App\Http\Controllers\Productos\ProductoController;
use App\Http\Controllers\Proveedores\ProveedorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Usuarios\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::middleware('permiso:ver_clientes')->group(function () {
        Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index');

        Route::middleware('permiso:gestionar_clientes')->group(function () {
            Route::post('clientes', [ClienteController::class, 'store'])->name('clientes.store');
            Route::put('clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
            Route::delete('clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
        });
    });

    Route::middleware('permiso:ver_compras')->group(function () {
        Route::get('compras', [CompraController::class, 'index'])->name('compras.index');

        Route::middleware('permiso:gestionar_compras')->group(function () {
            Route::post('compras', [CompraController::class, 'store'])->name('compras.store');
            Route::put('compras/{compra}', [CompraController::class, 'update'])->name('compras.update');
            Route::delete('compras/{compra}', [CompraController::class, 'destroy'])->name('compras.destroy');
        });
    });

    Route::middleware('permiso:ver_proveedores')->group(function () {
        Route::get('proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');

        Route::middleware('permiso:gestionar_proveedores')->group(function () {
            Route::post('proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
            Route::put('proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
            Route::delete('proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
        });
    });

    Route::middleware('permiso:ver_productos')->group(function () {
        Route::get('productos', [ProductoController::class, 'index'])->name('productos.index');

        Route::middleware('permiso:gestionar_productos')->group(function () {
            Route::post('productos', [ProductoController::class, 'store'])->name('productos.store');
            Route::put('productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
            Route::delete('productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
        });
    });

    Route::get('catalogo', [ProductoController::class, 'catalogo'])->name('catalogo');

    Route::middleware('permiso:gestionar_categorias')->group(function () {
        Route::get('categorias', [CategoriaController::class, 'index'])->name('categorias.index');
        Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::put('categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
    });

    Route::middleware('permiso:gestionar_usuarios')->group(function () {
        Route::get('usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::post('usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::put('usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
        Route::patch('usuarios/{user}/estado', [UserController::class, 'updateEstado'])->name('usuarios.estado');
        Route::delete('usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');
    });

    Route::middleware('permiso:gestionar_roles')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('roles/{rol}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{rol}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});

require __DIR__.'/settings.php';
