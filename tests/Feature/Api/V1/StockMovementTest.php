<?php

use App\Models\Product;
use App\Models\StockLevel;

it('records a purchase movement and increases stock level', function () {
    $product = Product::factory()->create();
    StockLevel::factory()->create(['product_id' => $product->id, 'quantity_on_hand' => 10]);
    actingAsUser();

    $this->postJson('/api/v1/stock-movements', [
        'product_id' => $product->id,
        'type' => 'purchase',
        'quantity' => 50,
    ])->assertCreated()
        ->assertJsonPath('data.quantity_before', '10.000')
        ->assertJsonPath('data.quantity_after', '60.000');

    expect(StockLevel::query()->where('product_id', $product->id)->value('quantity_on_hand'))
        ->toBe('60.000');
});

it('records a consumption movement and decreases stock level', function () {
    $product = Product::factory()->create();
    StockLevel::factory()->create(['product_id' => $product->id, 'quantity_on_hand' => 100]);
    actingAsUser();

    $this->postJson('/api/v1/stock-movements', [
        'product_id' => $product->id,
        'type' => 'consumption',
        'quantity' => 30,
    ])->assertCreated()
        ->assertJsonPath('data.quantity_after', '70.000');
});

it('rejects a consumption that would result in negative stock', function () {
    $product = Product::factory()->create();
    StockLevel::factory()->create(['product_id' => $product->id, 'quantity_on_hand' => 5]);
    actingAsUser();

    $this->postJson('/api/v1/stock-movements', [
        'product_id' => $product->id,
        'type' => 'consumption',
        'quantity' => 10,
    ])->assertUnprocessable();
});

it('records a return movement and increases stock level', function () {
    $product = Product::factory()->create();
    StockLevel::factory()->create(['product_id' => $product->id, 'quantity_on_hand' => 20]);
    actingAsUser();

    $this->postJson('/api/v1/stock-movements', [
        'product_id' => $product->id,
        'type' => 'return',
        'quantity' => 5,
    ])->assertCreated()
        ->assertJsonPath('data.quantity_after', '25.000');
});

it('lists movements filterable by product and type', function () {
    $product = Product::factory()->create();
    StockLevel::factory()->create(['product_id' => $product->id, 'quantity_on_hand' => 200]);
    actingAsUser();

    $this->postJson('/api/v1/stock-movements', ['product_id' => $product->id, 'type' => 'purchase', 'quantity' => 10]);
    $this->postJson('/api/v1/stock-movements', ['product_id' => $product->id, 'type' => 'consumption', 'quantity' => 5]);

    $this->getJson("/api/v1/stock-movements?product_id={$product->id}&type=purchase")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('shows a single movement with product and user', function () {
    $product = Product::factory()->create();
    StockLevel::factory()->create(['product_id' => $product->id, 'quantity_on_hand' => 50]);
    actingAsUser();

    $response = $this->postJson('/api/v1/stock-movements', [
        'product_id' => $product->id,
        'type' => 'purchase',
        'quantity' => 20,
    ]);

    $id = $response->json('data.id');

    $this->getJson("/api/v1/stock-movements/{$id}")
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['id', 'product', 'user', 'type', 'quantity']]);
});

it('returns 401 without auth', function () {
    $this->getJson('/api/v1/stock-movements')->assertUnauthorized();
});
