<?php

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;

class SaveFundTransferRequest extends FormRequest
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
            'from_category_id' => ['required', 'uuid', 'exists:savings_categories,id'],
            'to_category_id' => [
                'required',
                'uuid',
                'exists:savings_categories,id',
                'different:from_category_id',
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
            'transferred_on' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => __('Describe what this transfer was for.'),
            'to_category_id.different' => __('Choose a different fund bucket to transfer to.'),
        ];
    }
}
