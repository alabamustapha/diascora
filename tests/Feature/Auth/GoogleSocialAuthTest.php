<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('redirect to google returns a redirect', function () {
    Socialite::shouldReceive('driver->redirect')
        ->once()
        ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    $response = $this->get(route('auth.google'));

    $response->assertRedirect();
    expect($response->headers->get('location'))->toContain('accounts.google.com');
});

test('google callback creates new user and logs them in', function () {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-123');
    $socialiteUser->shouldReceive('getName')->andReturn('Jane Doe');
    $socialiteUser->shouldReceive('getEmail')->andReturn('jane@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'google_id' => 'google-123',
        'email' => 'jane@example.com',
    ]);
});

test('google callback finds existing user by google id and logs them in', function () {
    $existingUser = User::factory()->create([
        'google_id' => 'google-456',
        'email' => 'existing@example.com',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-456');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

    Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($existingUser);

    expect(User::query()->count())->toBe(1);
});

test('first user created via google callback gets sysadmin role', function () {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-first');
    $socialiteUser->shouldReceive('getName')->andReturn('First User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('first@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

    Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    $user = User::query()->where('google_id', 'google-first')->firstOrFail();

    expect($user->isSysadmin())->toBeTrue();
});

test('subsequent users created via google callback get no role', function () {
    User::factory()->create();

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-second');
    $socialiteUser->shouldReceive('getName')->andReturn('Second User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('second@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

    Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    $user = User::query()->where('google_id', 'google-second')->firstOrFail();

    expect($user->getRoleNames())->toBeEmpty();
});
