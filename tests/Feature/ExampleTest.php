<?php

use App\Models\User;

test('can see Dashboard in dashboard page', function () {
    $response = visit('/');

    $response->assertSee('Laravel');
});

test('authenticated user sees dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    visit('/dashboard')
        ->assertSee('Dashboard');
});


test('can see Job', function () {
    $response = visit('/');

    $response->assertSee('Job');
});
