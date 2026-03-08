<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guests are redirected to the login page', function () {
    $response = get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = get(route('dashboard'));
    $response
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('surveys', [])
                ->where('stats.total_surveys', 0)
                ->where('stats.total_responses', 0)
                ->has('questionTypes', 5),
        );
});

test('authenticated users can visit the survey library page', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = get(route('surveys.index'));

    $response
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('surveys/Index')
                ->where('surveys', [])
                ->where('stats.total_surveys', 0),
        );
});
