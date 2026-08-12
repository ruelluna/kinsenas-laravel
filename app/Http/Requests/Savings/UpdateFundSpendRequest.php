<?php

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFundSpendRequest extends FormRequest
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
            'recipient_id' => ['nullable', 'uuid', 'exists:recipients,id'],
            'receipt_image' => ['nullable', 'image', 'max:5120'],
            'remove_receipt' => ['sometimes', 'boolean'],
            'expects_reimbursement' => ['sometimes', 'boolean'],
            'expected_from_recipient_id' => [
                Rule::requiredIf(fn () => $this->boolean('expects_reimbursement')),
                'nullable',
                'uuid',
                'exists:recipients,id',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => __('Describe what this spending was for.'),
            'expected_from_recipient_id.required' => __('Select who will pay you back.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $recipientId = $this->input('recipient_id');
        $expectedFromRecipientId = $this->input('expected_from_recipient_id');

        if ($recipientId === '') {
            $this->merge(['recipient_id' => null]);
        }

        if ($expectedFromRecipientId === '') {
            $this->merge(['expected_from_recipient_id' => null]);
        }
    }
}
