<?php

namespace App\Http\Requests\Api\V1\UnitOfMeasure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitOfMeasureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('units_of_measure', 'name')],
            'abbreviation' => ['required', 'string', 'max:10', Rule::unique('units_of_measure', 'abbreviation')],
        ];
    }
}
