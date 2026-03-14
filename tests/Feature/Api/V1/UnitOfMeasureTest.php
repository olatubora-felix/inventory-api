<?php

use App\Models\UnitOfMeasure;

it('lists units of measure for authenticated users', function () {
    UnitOfMeasure::factory()->count(3)->create();
    actingAsUser();

    $this->getJson('/api/v1/units-of-measure')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('returns 401 when unauthenticated', function () {
    $this->getJson('/api/v1/units-of-measure')->assertUnauthorized();
});

it('shows a single unit of measure', function () {
    $unit = UnitOfMeasure::factory()->create();
    actingAsUser();

    $this->getJson("/api/v1/units-of-measure/{$unit->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.abbreviation', $unit->abbreviation);
});

it('allows admin to create a unit of measure', function () {
    actingAsAdmin();

    $this->postJson('/api/v1/units-of-measure', ['name' => 'Kilogram', 'abbreviation' => 'kg'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Kilogram');
});

it('returns 403 when non-admin creates a unit of measure', function () {
    actingAsUser();

    $this->postJson('/api/v1/units-of-measure', ['name' => 'Kilogram', 'abbreviation' => 'kg'])
        ->assertForbidden();
});

it('validates name and abbreviation uniqueness', function () {
    UnitOfMeasure::factory()->create(['name' => 'Kilogram', 'abbreviation' => 'kg']);
    actingAsAdmin();

    $this->postJson('/api/v1/units-of-measure', ['name' => 'Kilogram', 'abbreviation' => 'kg'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'abbreviation']);
});

it('allows admin to update a unit of measure', function () {
    $unit = UnitOfMeasure::factory()->create();
    actingAsAdmin();

    $this->putJson("/api/v1/units-of-measure/{$unit->id}", ['name' => 'Gram', 'abbreviation' => 'g'])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Gram');
});

it('allows admin to delete a unit of measure', function () {
    $unit = UnitOfMeasure::factory()->create();
    actingAsAdmin();

    $this->deleteJson("/api/v1/units-of-measure/{$unit->id}")
        ->assertSuccessful()
        ->assertJsonPath('success', true);
});
