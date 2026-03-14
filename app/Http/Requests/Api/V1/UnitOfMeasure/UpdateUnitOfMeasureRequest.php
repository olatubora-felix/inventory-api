<?php

namespace App\Http\Requests\Api\V1\UnitOfMeasure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitOfMeasureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('units_of_measure', 'name')->ignore($this->route('unit_of_measure'))],
            'abbreviation' => ['required', 'string', 'max:10', Rule::unique('units_of_measure', 'abbreviation')->ignore($this->route('unit_of_measure'))],
        ];
    }
}
