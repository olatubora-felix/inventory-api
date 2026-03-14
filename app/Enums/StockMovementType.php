<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Purchase = 'purchase';
    case Consumption = 'consumption';
    case Adjustment = 'adjustment';
    case Waste = 'waste';
    case Return = 'return';

    public function increasesStock(): bool
    {
        return match ($this) {
            self::Purchase, self::Return => true,
            default => false,
        };
    }
}
