<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->seed(RolesSeeder::class);

    $user = userWithRole(User::ROL_CAJERA);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});
