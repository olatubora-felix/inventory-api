<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockLevelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'quantity_on_hand' => $this->quantity_on_hand,
            'last_updated_at' => $this->last_updated_at,
        ];
    }
}
