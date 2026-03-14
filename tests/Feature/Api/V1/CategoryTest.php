<?php

use App\Models\Category;

it('returns 401 for unauthenticated list request', function () {
    $this->getJson('/api/v1/categories')->assertUnauthorized();
});

it('lists categories for authenticated users', function () {
    Category::factory()->count(3)->create();
    actingAsUser();

    $this->getJson('/api/v1/categories')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data');
});

it('shows a single category', function () {
    $category = Category::factory()->create();
    actingAsUser();

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.name', $category->name);
});

it('allows admin to create a category', function () {
    actingAsAdmin();

    $this->postJson('/api/v1/categories', ['name' => 'Proteins', 'description' => 'Meat items'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Proteins');

    $this->assertDatabaseHas('categories', ['name' => 'Proteins']);
});

it('returns 403 when non-admin tries to create a category', function () {
    actingAsUser();

    $this->postJson('/api/v1/categories', ['name' => 'Proteins'])->assertForbidden();
});

it('validates required fields on category create', function () {
    actingAsAdmin();

    $this->postJson('/api/v1/categories', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('validates category name uniqueness', function () {
    Category::factory()->create(['name' => 'Proteins']);
    actingAsAdmin();

    $this->postJson('/api/v1/categories', ['name' => 'Proteins'])->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('allows admin to update a category', function () {
    $category = Category::factory()->create();
    actingAsAdmin();

    $this->putJson("/api/v1/categories/{$category->id}", ['name' => 'Updated'])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated');
});

it('allows admin to delete a category', function () {
    $category = Category::factory()->create();
    actingAsAdmin();

    $this->deleteJson("/api/v1/categories/{$category->id}")
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
