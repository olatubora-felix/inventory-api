<?php

namespace App\Models;

use Database\Factories\UnitOfMeasureFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    /** @use HasFactory<UnitOfMeasureFactory> */
    use HasFactory, HasUuids;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'name',
        'abbreviation',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
