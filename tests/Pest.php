<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function actingAsAdmin(): User
{
    return Sanctum::actingAs(User::factory()->admin()->create());
}

function actingAsUser(): User
{
    return Sanctum::actingAs(User::factory()->create());
}
