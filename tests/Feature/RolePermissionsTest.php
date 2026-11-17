<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Support\Facades\Route;

beforeEach(fn () => $this->seed(RolesSeeder::class));

it('admin can access clientes index', function () {
    $user = userWithRole(User::ROL_ADMINISTRADOR);

    $response = $this->actingAs($user)->get(route('clientes.index'));

    $response->assertOk();
});

it('cajera can access clientes index', function () {
    $user = userWithRole(User::ROL_CAJERA);

    $response = $this->actingAs($user)->get(route('clientes.index'));

    $response->assertOk();
});

it('operador can access clientes index', function () {
    $user = userWithRole(User::ROL_OPERADOR);

    $response = $this->actingAs($user)->get(route('clientes.index'));

    $response->assertOk();
});

it('admin can create clientes', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->post(route('clientes.store'), [
            'nombre' => 'Test Cliente',
            'email' => 'cliente@test.com',
        ])
        ->assertRedirect();
});

it('cajera gets 403 on crear cliente', function () {
    $cajera = userWithRole(User::ROL_CAJERA);

    $this->actingAs($cajera)
        ->post(route('clientes.store'), [
            'nombre' => 'Test Cliente',
            'email' => 'cliente@test.com',
        ])
        ->assertForbidden();
});

it('operador gets 403 on crear cliente', function () {
    $operador = userWithRole(User::ROL_OPERADOR);

    $this->actingAs($operador)
        ->post(route('clientes.store'), [
            'nombre' => 'Test Cliente',
            'email' => 'cliente@test.com',
        ])
        ->assertForbidden();
});

it('admin can create compras', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->post(route('compras.store'), [
            'proveedor' => 'Test Proveedor',
            'producto' => 'Harina',
            'cantidad' => 10,
            'precio_unitario' => 50,
            'fecha_compra' => '2026-06-16',
            'estado' => 'pendiente',
        ])
        ->assertRedirect();
});

it('admin has full access even if role permissions are incomplete', function () {
    Route::get('/test/admin-full-access', fn () => 'ok')
        ->middleware(['web', 'auth', 'permiso:gestionar_productos']);

    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $admin->rol()->update(['permisos' => []]);

    $this->actingAs($admin)
        ->get('/test/admin-full-access')
        ->assertOk();
});

it('unknown permissions are denied by default', function () {
    Route::get('/test/unknown-permission', fn () => 'ok')
        ->middleware(['web', 'auth', 'permiso:gestionar_desconocido']);

    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->get('/test/unknown-permission')
        ->assertForbidden();
});

it('cajera can create compras', function () {
    $cajera = userWithRole(User::ROL_CAJERA);

    $this->actingAs($cajera)
        ->post(route('compras.store'), [
            'proveedor' => 'Test Proveedor',
            'producto' => 'Harina',
            'cantidad' => 10,
            'precio_unitario' => 50,
            'fecha_compra' => '2026-06-16',
            'estado' => 'pendiente',
        ])
        ->assertRedirect();
});

it('operador gets 403 on crear compra', function () {
    $operador = userWithRole(User::ROL_OPERADOR);

    $this->actingAs($operador)
        ->post(route('compras.store'), [
            'proveedor' => 'Test Proveedor',
            'producto' => 'Harina',
            'cantidad' => 10,
            'precio_unitario' => 50,
            'fecha_compra' => '2026-06-16',
            'estado' => 'pendiente',
        ])
        ->assertForbidden();
});
