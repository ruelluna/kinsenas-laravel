<?php

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;

class RecordFundSpendReimbursementRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'received_on' => ['required', 'date'],
            'bank_id' => ['nullable', 'uuid', 'exists:banks,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $bankId = $this->input('bank_id');

        if ($bankId === '') {
            $this->merge(['bank_id' => null]);
        }
    }
}
