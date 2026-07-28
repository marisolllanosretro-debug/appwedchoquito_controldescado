<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(fn () => $this->seed(RolesSeeder::class));

it('admin can manage usuarios', function () {
    $user = userWithRole(User::ROL_ADMINISTRADOR);

    expect($user->esAdministrador())->toBeTrue();
});

it('cajera cannot manage usuarios', function () {
    $user = userWithRole(User::ROL_CAJERA);

    expect($user->esAdministrador())->toBeFalse();
});

it('admin can access usuarios index', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->get('/usuarios')
        ->assertSuccessful();
});

it('cajera gets 403 on usuarios index', function () {
    $cajera = userWithRole(User::ROL_CAJERA);

    $this->actingAs($cajera)
        ->get('/usuarios')
        ->assertForbidden();
});

it('admin can create a new user', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'id_rol' => User::ROL_CAJERA,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);

    $user = User::where('email', 'test@example.com')->first();
    expect($user->isActivo())->toBeTrue();
    expect($user->hasRol(User::ROL_CAJERA))->toBeTrue();
});

it('admin can update a user', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $target = User::factory()->create(['id_rol' => User::ROL_CAJERA]);

    $this->actingAs($admin)
        ->put("/usuarios/{$target->id}", [
            'name' => 'Updated Name',
            'email' => $target->email,
            'password' => '',
            'id_rol' => User::ROL_OPERADOR,
        ])
        ->assertRedirect();

    expect($target->fresh()->name)->toBe('Updated Name');
    expect($target->fresh()->hasRol(User::ROL_OPERADOR))->toBeTrue();
});

it('admin cannot self-remove role', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->put("/usuarios/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'id_rol' => User::ROL_CAJERA,
        ])
        ->assertRedirect();

    expect($admin->fresh()->hasRol(User::ROL_ADMINISTRADOR))->toBeTrue();
});

it('admin can delete a non-admin user', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $target = User::factory()->create(['id_rol' => User::ROL_CAJERA]);

    $this->actingAs($admin)
        ->delete("/usuarios/{$target->id}")
        ->assertRedirect();

    expect($target->fresh())->toBeNull();
});

it('cannot delete the last admin', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->delete("/usuarios/{$admin->id}")
        ->assertRedirect();

    expect($admin->fresh())->not->toBeNull();
});

it('can delete admin if another admin exists', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $otherAdmin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->delete("/usuarios/{$otherAdmin->id}")
        ->assertRedirect();

    expect($otherAdmin->fresh())->toBeNull();
});

it('cajera cannot create user', function () {
    $cajera = userWithRole(User::ROL_CAJERA);

    $this->actingAs($cajera)
        ->post('/usuarios', [
            'name' => 'Hacker',
            'email' => 'hacker@test.com',
            'password' => 'password123',
            'id_rol' => User::ROL_ADMINISTRADOR,
        ])
        ->assertForbidden();
});

it('admin can deactivate another user', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $target = User::factory()->create(['id_rol' => User::ROL_CAJERA, 'activo' => true]);

    $this->actingAs($admin)
        ->patch("/usuarios/{$target->id}/estado", [
            'activo' => false,
        ])
        ->assertRedirect();

    expect($target->fresh()->isActivo())->toBeFalse();
});

it('admin can reactivate a user', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    $target = User::factory()->create(['id_rol' => User::ROL_CAJERA, 'activo' => false]);

    $this->actingAs($admin)
        ->patch("/usuarios/{$target->id}/estado", [
            'activo' => true,
        ])
        ->assertRedirect();

    expect($target->fresh()->isActivo())->toBeTrue();
});

it('cannot self-deactivate', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->patch("/usuarios/{$admin->id}/estado", [
            'activo' => false,
        ])
        ->assertRedirect();

    expect($admin->fresh()->isActivo())->toBeTrue();
});

it('inactive user cannot login', function () {
    $this->seed(RolesSeeder::class);

    $user = User::factory()->create([
        'email' => 'inactive@test.com',
        'password' => bcrypt('password'),
        'activo' => false,
        'id_rol' => User::ROL_CAJERA,
    ]);

    $this->post('/login', [
        'email' => 'inactive@test.com',
        'password' => 'password',
    ]);

    $this->assertGuest();
});

it('validates required fields on create', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->post('/usuarios', [])
        ->assertInvalid(['name', 'email', 'password', 'id_rol']);
});

it('validates email uniqueness on create', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);
    User::factory()->create(['email' => 'duplicate@test.com']);

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'Test',
            'email' => 'duplicate@test.com',
            'password' => 'password123',
            'id_rol' => User::ROL_CAJERA,
        ])
        ->assertInvalid(['email']);
});

it('validates role must be valid on create', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'password123',
            'id_rol' => 99,
        ])
        ->assertInvalid(['id_rol']);
});

it('newly created user can login successfully', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'id_rol' => User::ROL_CAJERA,
        ])
        ->assertRedirect();

    $this->post('/login', [
        'email' => 'newuser@test.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();
});

it('login shows welcome flash based on role', function () {
    $this->seed(RolesSeeder::class);

    $user = User::factory()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'id_rol' => User::ROL_ADMINISTRADOR,
    ]);

    $this->post('/login', [
        'email' => 'admin@test.com',
        'password' => 'password',
    ]);

    expect(session('welcome'))->toContain('Administrador');
});

it('login shows cajera welcome flash', function () {
    $this->seed(RolesSeeder::class);

    $user = User::factory()->create([
        'email' => 'cajera@test.com',
        'password' => bcrypt('password'),
        'id_rol' => User::ROL_CAJERA,
    ]);

    $this->post('/login', [
        'email' => 'cajera@test.com',
        'password' => 'password',
    ]);

    expect(session('welcome'))->toContain('Cajera');
});

it('validates password min 8 chars', function () {
    $admin = userWithRole(User::ROL_ADMINISTRADOR);

    $this->actingAs($admin)
        ->post('/usuarios', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'short',
            'id_rol' => User::ROL_CAJERA,
        ])
        ->assertInvalid(['password']);
});
