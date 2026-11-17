<?php

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(fn () => $this->seed(RolesSeeder::class));

function productoParaCatalogo(array $attributes = []): Producto
{
    return Producto::query()->create([
        'nombre' => 'Pan de prueba',
        'categoria' => 'Panificados',
        'descripcion' => 'Producto disponible para pedidos.',
        'precio_compra' => 5,
        'precio_venta' => 10,
        'stock' => 20,
        'stock_minimo' => 2,
        'unidad_medida' => 'Unidad',
        ...$attributes,
    ]);
}

function datosPedido(Producto $producto, int $cantidad = 2): array
{
    return [
        'cliente_nombre' => 'Cliente Prueba',
        'cliente_email' => 'cliente@example.com',
        'cliente_telefono' => '70000000',
        'direccion' => 'Calle principal 123',
        'metodo_pago' => 'efectivo',
        'items' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
            ],
        ],
    ];
}

it('allows an admin to update a catalog sale price', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $producto = productoParaCatalogo();

    $this->actingAs($admin)
        ->patch(route('catalogo.productos.precio', $producto), [
            'precio_venta' => 18.50,
        ])
        ->assertRedirect();

    expect($producto->fresh()->precio_venta)->toBe('18.50');
});

it('allows a guest to browse the catalog', function () {
    productoParaCatalogo();

    $this->get(route('catalogo'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Catalogo/index')
            ->has('productos', 1));
});

it('allows a guest to complete and view their order without registering', function () {
    $producto = productoParaCatalogo(['precio_venta' => 12.50, 'stock' => 10]);

    $response = $this->post(route('pedidos.store'), datosPedido($producto, 3));
    $pedido = Pedido::query()->sole();

    $response->assertRedirect(route('pedidos.show', $pedido));
    expect($pedido->user_id)->toBeNull()
        ->and($pedido->total)->toBe('37.50')
        ->and($producto->fresh()->stock)->toBe(7);

    $this->get(route('pedidos.show', $pedido))->assertSuccessful();
});

it('prevents a different guest session from viewing an order', function () {
    $producto = productoParaCatalogo();

    $this->post(route('pedidos.store'), datosPedido($producto));
    $pedido = Pedido::query()->sole();

    $this->flushSession();

    $this->get(route('pedidos.show', $pedido))->assertForbidden();
});

it('allows an admin to add a product to an active catalog category', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $categoria = Categoria::query()->create([
        'nombre' => 'Panificados',
        'icono' => '🥖',
        'activo' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('productos.store'), [
            'nombre' => 'Pan de queso',
            'id_categoria' => $categoria->id,
            'categoria' => 'Categoría falsificada',
            'descripcion' => 'Pan recién horneado.',
            'precio_venta' => 7.50,
            'stock' => 24,
            'stock_minimo' => 5,
            'unidad_medida' => 'Unidad',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $producto = Producto::query()->where('nombre', 'Pan de queso')->sole();

    expect($producto->id_categoria)->toBe($categoria->id)
        ->and($producto->categoria)->toBe($categoria->nombre)
        ->and($producto->precio_venta)->toBe('7.50')
        ->and($producto->precio_compra)->toBe('0.00');
});

it('forbids a cashier from adding products to the catalog', function () {
    $cajera = userWithRole(User::ROL_CAJERA);
    $categoria = Categoria::query()->create([
        'nombre' => 'Panificados',
        'icono' => '🥖',
        'activo' => true,
    ]);

    $this->actingAs($cajera)
        ->post(route('productos.store'), [
            'nombre' => 'Producto sin permiso',
            'id_categoria' => $categoria->id,
            'precio_venta' => 10,
            'stock' => 5,
            'stock_minimo' => 1,
            'unidad_medida' => 'Unidad',
        ])
        ->assertForbidden();

    expect(Producto::query()->where('nombre', 'Producto sin permiso')->exists())->toBeFalse();
});

it('rejects an inactive category when adding a catalog product', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $categoria = Categoria::query()->create([
        'nombre' => 'Categoría inactiva',
        'activo' => false,
    ]);

    $this->actingAs($admin)
        ->post(route('productos.store'), [
            'nombre' => 'Producto inválido',
            'id_categoria' => $categoria->id,
            'precio_venta' => 10,
            'stock' => 5,
            'stock_minimo' => 1,
            'unidad_medida' => 'Unidad',
        ])
        ->assertInvalid(['id_categoria']);

    expect(Producto::query()->where('nombre', 'Producto inválido')->exists())->toBeFalse();
});

it('forbids a cashier from updating catalog prices', function () {
    $cajera = userWithRole(User::ROL_CAJERA);
    $producto = productoParaCatalogo();

    $this->actingAs($cajera)
        ->patch(route('catalogo.productos.precio', $producto), [
            'precio_venta' => 18.50,
        ])
        ->assertForbidden();

    expect($producto->fresh()->precio_venta)->toBe('10.00');
});

it('registers a completed order using server prices and decrements stock', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $producto = productoParaCatalogo(['precio_venta' => 12.50, 'stock' => 10]);

    $response = $this->actingAs($admin)
        ->post(route('pedidos.store'), datosPedido($producto, 3));

    $pedido = Pedido::query()->with('detalles')->sole();

    $response->assertRedirect(route('pedidos.show', $pedido));
    expect($pedido->estado)->toBe('completado')
        ->and($pedido->total)->toBe('37.50')
        ->and($pedido->detalles)->toHaveCount(1)
        ->and($pedido->detalles->first()->precio_unitario)->toBe('12.50')
        ->and($producto->fresh()->stock)->toBe(7);
});

it('rejects an order without enough stock and rolls back all changes', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $producto = productoParaCatalogo(['stock' => 2]);

    $this->actingAs($admin)
        ->post(route('pedidos.store'), datosPedido($producto, 3))
        ->assertInvalid(['items.0.cantidad']);

    expect(Pedido::query()->count())->toBe(0)
        ->and($producto->fresh()->stock)->toBe(2);
});

it('keeps the historical sale price after the product price changes', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $producto = productoParaCatalogo(['precio_venta' => 15]);

    $this->actingAs($admin)
        ->post(route('pedidos.store'), datosPedido($producto));

    $pedido = Pedido::query()->with('detalles')->sole();
    $producto->update(['precio_venta' => 25]);

    expect($pedido->detalles->first()->precio_unitario)->toBe('15.00')
        ->and($pedido->total)->toBe('30.00');
});
