<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'uuid', Rule::exists('categories', 'id')],
            'unit_of_measure_id' => ['required', 'uuid', Rule::exists('units_of_measure', 'id')],
            'reorder_level' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => [Rule::exists('suppliers', 'id')],
        ];
    }
}
