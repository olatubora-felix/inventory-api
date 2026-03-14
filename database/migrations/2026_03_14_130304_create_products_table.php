<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('sku', 100)->unique();
            $table->text('description')->nullable();
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('unit_of_measure_id')->constrained('units_of_measure');
            $table->decimal('reorder_level', 10, 3)->default(0);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
