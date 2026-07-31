<?php

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;

class SaveIncomePeriodDeductionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'custom_amounts' => ['required', 'array'],
            'custom_amounts.*.category_id' => ['required', 'uuid'],
            'custom_amounts.*.amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
