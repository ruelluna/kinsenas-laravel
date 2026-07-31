<?php

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;

class SaveTransferRequest extends FormRequest
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
            'income_period_id' => ['required', 'uuid', 'exists:income_periods,id'],
            'category_id' => ['required', 'uuid', 'exists:savings_categories,id'],
            'bank_id' => ['required', 'uuid', 'exists:banks,id'],
            'recipient_id' => ['required', 'uuid', 'exists:recipients,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transferred_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
