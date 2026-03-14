<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\UnitOfMeasure;

it('lists products with stock levels eager loaded', function () {
    $product = Product::factory()->create();
    StockLevel::factory()->create(['product_id' => $product->id]);
    actingAsUser();

    $this->getJson('/api/v1/products')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => [['id', 'stock_level']]]);
});

it('returns 401 when unauthenticated', function () {
    $this->getJson('/api/v1/products')->assertUnauthorized();
});

it('filters products by category', function () {
    $cat = Category::factory()->create();
    Product::factory()->count(2)->create(['category_id' => $cat->id]);
    Product::factory()->create();
    actingAsUser();

    $this->getJson("/api/v1/products?category_id={$cat->id}")
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('shows a product with its suppliers and stock level', function () {
    $product = Product::factory()->create();
    StockLevel::factory()->create(['product_id' => $product->id]);
    actingAsUser();

    $this->getJson("/api/v1/products/{$product->id}")
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['id', 'sku', 'category', 'unit_of_measure', 'stock_level', 'suppliers']]);
});

it('allows admin to create a product and initialises its stock level', function () {
    $category = Category::factory()->create();
    $unit = UnitOfMeasure::factory()->create();
    actingAsAdmin();

    $this->postJson('/api/v1/products', [
        'name' => 'Chicken Breast',
        'sku' => 'CHK-001',
        'category_id' => $category->id,
        'unit_of_measure_id' => $unit->id,
        'reorder_level' => 10,
    ])->assertCreated();

    $this->assertDatabaseHas('stock_levels', [
        'quantity_on_hand' => 0,
    ]);
});

it('returns 403 when non-admin creates a product', function () {
    $category = Category::factory()->create();
    $unit = UnitOfMeasure::factory()->create();
    actingAsUser();

    $this->postJson('/api/v1/products', [
        'name' => 'Chicken Breast',
        'sku' => 'CHK-001',
        'category_id' => $category->id,
        'unit_of_measure_id' => $unit->id,
        'reorder_level' => 10,
    ])->assertForbidden();
});

it('validates sku uniqueness on create', function () {
    Product::factory()->create(['sku' => 'DUPE-001']);
    $category = Category::factory()->create();
    $unit = UnitOfMeasure::factory()->create();
    actingAsAdmin();

    $this->postJson('/api/v1/products', [
        'name' => 'Another Product',
        'sku' => 'DUPE-001',
        'category_id' => $category->id,
        'unit_of_measure_id' => $unit->id,
        'reorder_level' => 5,
    ])->assertUnprocessable()->assertJsonValidationErrors(['sku']);
});

it('returns low stock products at the correct endpoint', function () {
    $product = Product::factory()->create(['reorder_level' => 20]);
    StockLevel::factory()->create(['product_id' => $product->id, 'quantity_on_hand' => 5]);

    $productOk = Product::factory()->create(['reorder_level' => 5]);
    StockLevel::factory()->create(['product_id' => $productOk->id, 'quantity_on_hand' => 100]);

    actingAsUser();

    $this->getJson('/api/v1/products/low-stock')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $product->id);
});

it('allows admin to soft-delete a product', function () {
    $product = Product::factory()->create();
    actingAsAdmin();

    $this->deleteJson("/api/v1/products/{$product->id}")
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});
