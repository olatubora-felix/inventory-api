<?php

use App\Models\Supplier;

it('lists suppliers for authenticated users', function () {
    Supplier::factory()->count(3)->create();
    actingAsUser();

    $this->getJson('/api/v1/suppliers')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('returns 401 when unauthenticated', function () {
    $this->getJson('/api/v1/suppliers')->assertUnauthorized();
});

it('shows a single supplier', function () {
    $supplier = Supplier::factory()->create();
    actingAsUser();

    $this->getJson("/api/v1/suppliers/{$supplier->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.name', $supplier->name);
});

it('allows admin to create a supplier', function () {
    actingAsAdmin();

    $this->postJson('/api/v1/suppliers', [
        'name' => 'Fresh Foods Ltd',
        'email' => 'contact@freshfoods.com',
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Fresh Foods Ltd');
});

it('returns 403 when non-admin creates a supplier', function () {
    actingAsUser();

    $this->postJson('/api/v1/suppliers', ['name' => 'Test Supplier'])->assertForbidden();
});

it('validates email uniqueness on create', function () {
    Supplier::factory()->create(['email' => 'used@example.com']);
    actingAsAdmin();

    $this->postJson('/api/v1/suppliers', ['name' => 'New Supplier', 'email' => 'used@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('allows admin to update a supplier', function () {
    $supplier = Supplier::factory()->create();
    actingAsAdmin();

    $this->putJson("/api/v1/suppliers/{$supplier->id}", ['name' => 'Updated Name', 'is_active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name');
});

it('soft-deletes a supplier on destroy', function () {
    $supplier = Supplier::factory()->create();
    actingAsAdmin();

    $this->deleteJson("/api/v1/suppliers/{$supplier->id}")
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});
