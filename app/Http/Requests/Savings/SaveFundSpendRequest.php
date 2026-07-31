<?php

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFundSpendRequest extends FormRequest
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
            'category_id' => ['required', 'uuid', 'exists:savings_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
            'spent_on' => ['required', 'date'],
            'bank_id' => ['nullable', 'uuid', 'exists:banks,id'],
            'recipient_id' => ['nullable', 'uuid', 'exists:recipients,id'],
            'receipt_image' => ['nullable', 'image', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => __('Describe what this spending was for.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $bankId = $this->input('bank_id');
        $recipientId = $this->input('recipient_id');

        if ($bankId === '') {
            $this->merge(['bank_id' => null]);
        }

        if ($recipientId === '') {
            $this->merge(['recipient_id' => null]);
        }
    }
}
