<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'unit_of_measure' => new UnitOfMeasureResource($this->whenLoaded('unitOfMeasure')),
            'reorder_level' => $this->reorder_level,
            'cost_price' => $this->cost_price,
            'is_active' => $this->is_active,
            'stock_level' => new StockLevelResource($this->whenLoaded('stockLevel')),
            'suppliers' => SupplierResource::collection($this->whenLoaded('suppliers')),
            'created_at' => $this->created_at,
        ];
    }
}
