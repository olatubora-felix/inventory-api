<?php

it('returns inventory summary for admin', function () {
    actingAsAdmin();

    $this->getJson('/api/v1/reports/inventory-summary')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['total_products', 'active_products', 'low_stock_count', 'by_category']]);
});

it('returns stock value report for admin', function () {
    actingAsAdmin();

    $this->getJson('/api/v1/reports/stock-value')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['items', 'grand_total']]);
});

it('returns 403 for non-admin on inventory summary', function () {
    actingAsUser();

    $this->getJson('/api/v1/reports/inventory-summary')->assertForbidden();
});

it('returns 403 for non-admin on stock value', function () {
    actingAsUser();

    $this->getJson('/api/v1/reports/stock-value')->assertForbidden();
});

it('returns 401 when unauthenticated on reports', function () {
    $this->getJson('/api/v1/reports/inventory-summary')->assertUnauthorized();
});
