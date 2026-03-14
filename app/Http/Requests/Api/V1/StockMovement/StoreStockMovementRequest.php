<?php

namespace App\Http\Requests\Api\V1\StockMovement;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', Rule::exists('products', 'id')],
            'type' => ['required', 'string', Rule::enum(StockMovementType::class)],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }
}
