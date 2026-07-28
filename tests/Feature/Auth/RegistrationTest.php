<?php

test('registration screen returns 404 because registration is disabled', function () {
    $this->get('/register')->assertNotFound();
});

test('registration post returns 404 because registration is disabled', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});
