<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('isSysadmin returns true for user with sysadmin role', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Sysadmin->value);

    expect($user->isSysadmin())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
    expect($user->isManager())->toBeFalse();
});

test('isAdmin returns true for user with admin role', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin->value);

    expect($user->isAdmin())->toBeTrue();
    expect($user->isSysadmin())->toBeFalse();
    expect($user->isManager())->toBeFalse();
});

test('isManager returns true for user with manager role', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Manager->value);

    expect($user->isManager())->toBeTrue();
    expect($user->isSysadmin())->toBeFalse();
    expect($user->isAdmin())->toBeFalse();
});

test('first user registered via email gets sysadmin role', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'First User',
        'email' => 'first@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'first@example.com')->firstOrFail();

    expect($user->isSysadmin())->toBeTrue();
});

test('second user registered via email gets no role', function () {
    User::factory()->create();

    $response = $this->post(route('register.store'), [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'second@example.com')->firstOrFail();

    expect($user->isSysadmin())->toBeFalse();
    expect($user->getRoleNames())->toBeEmpty();
});

test('regular user has no elevated role by default', function () {
    $user = User::factory()->create();

    expect($user->isSysadmin())->toBeFalse();
    expect($user->isAdmin())->toBeFalse();
    expect($user->isManager())->toBeFalse();
});
