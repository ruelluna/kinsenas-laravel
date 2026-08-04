<?php

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;

class SaveBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        $this->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'bank_institution_id' => $this->input('bank_institution_id') ?: null,
            'account_label' => is_string($this->input('account_label'))
                ? trim($this->input('account_label')) ?: null
                : $this->input('account_label'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required_without:bank_institution_id', 'nullable', 'string', 'max:255'],
            'bank_institution_id' => ['nullable', 'uuid', 'exists:bank_institutions,id'],
            'account_label' => ['nullable', 'string', 'max:255'],
            'main_label' => ['nullable', 'string', 'max:255'],
            'spaces' => ['nullable', 'array', 'max:5'],
            'spaces.*.label' => ['required_with:spaces', 'string', 'max:255'],
            'spaces.*.enabled' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
