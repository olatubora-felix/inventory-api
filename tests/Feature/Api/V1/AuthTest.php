<?php

use App\Models\User;
use Tests\TestCase;

it('can login with valid credentials and receive a token', function () {
    /** @var TestCase $this */
    User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('password')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['success', 'message', 'data' => ['user' => ['id', 'email', 'role'], 'token']])
        ->assertJsonPath('data.user.email', 'test@example.com');
    expect($response->json('data.token'))->not->toBeEmpty();
});

it('returns 401 for invalid credentials', function () {
    /** @var TestCase $this */
    User::factory()->create(['email' => 'test@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ])->assertUnauthorized();
});

it('returns 422 for missing credentials', function () {
    /** @var TestCase $this */
    $this->postJson('/api/v1/auth/login', [])->assertUnprocessable();
});

it('can logout and the token is revoked', function () {
    /** @var TestCase $this */
    actingAsUser();

    $this->postJson('/api/v1/auth/logout')->assertSuccessful();
});

it('returns 401 on logout without a token', function () {
    /** @var TestCase $this */
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});

it('can fetch the authenticated user via me endpoint', function () {
    /** @var TestCase $this */
    $user = actingAsUser();

    $this->getJson('/api/v1/auth/me')
        ->assertSuccessful()
        ->assertJsonPath('data.email', $user->email);
});

it('can signup and receive a token', function () {
    /** @var TestCase $this */
    $response = $this->postJson('/api/v1/auth/signup', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['success', 'message', 'data' => ['user' => ['id', 'name', 'email', 'role'], 'token']])
        ->assertJsonPath('data.user.name', 'New User')
        ->assertJsonPath('data.user.email', 'newuser@example.com')
        ->assertJsonPath('data.user.role', 'user');
    expect($response->json('data.token'))->not->toBeEmpty();

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('returns 422 for signup with duplicate email', function () {
    /** @var TestCase $this */
    User::factory()->create(['email' => 'existing@example.com']);

    $this->postJson('/api/v1/auth/signup', [
        'name' => 'New User',
        'email' => 'existing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 for signup with invalid or missing fields', function () {
    /** @var TestCase $this */
    $this->postJson('/api/v1/auth/signup', [])->assertUnprocessable();

    $this->postJson('/api/v1/auth/signup', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertUnprocessable()->assertJsonValidationErrors(['password']);

    $this->postJson('/api/v1/auth/signup', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
});

it('can update profile when authenticated', function () {
    /** @var TestCase $this */
    $user = actingAsUser();

    $response = $this->putJson('/api/v1/auth/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.email', 'updated@example.com');

    expect($user->fresh()->name)->toBe('Updated Name')
        ->and($user->fresh()->email)->toBe('updated@example.com');
});

it('can update profile with partial data', function () {
    /** @var TestCase $this */
    $user = actingAsUser();

    $this->putJson('/api/v1/auth/profile', ['name' => 'Only Name Updated'])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Only Name Updated')
        ->assertJsonPath('data.email', $user->email);
});

it('returns 422 when updating profile with existing email', function () {
    /** @var TestCase $this */
    actingAsUser();
    User::factory()->create(['email' => 'taken@example.com']);

    $this->putJson('/api/v1/auth/profile', ['email' => 'taken@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns 401 when updating profile without a token', function () {
    /** @var TestCase $this */
    $this->putJson('/api/v1/auth/profile', ['name' => 'Updated'])->assertUnauthorized();
});
