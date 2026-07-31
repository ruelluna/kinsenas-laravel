<?php

namespace App\Http\Requests\Savings;

use App\Enums\CategoryAllocationType;
use App\Enums\DeductionMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveSavingsPlanRequest extends FormRequest
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
            'categories' => ['required', 'array', 'min:1'],
            'categories.*.id' => ['nullable', 'uuid'],
            'categories.*.name' => ['required', 'string', 'max:255'],
            'categories.*.allocation_type' => ['required', Rule::enum(CategoryAllocationType::class)],
            'categories.*.percentage' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'categories.*.deduction_mode' => ['nullable', Rule::enum(DeductionMode::class)],
            'categories.*.deduction_value' => ['nullable', 'numeric', 'min:0.01'],
            'categories.*.deduct_from_index' => ['nullable', 'integer', 'min:0'],
            'categories.*.bank_id' => ['nullable', 'uuid', 'exists:banks,id'],
            'is_shared_with_team' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $categories = $this->input('categories', []);

        foreach ($categories as $index => $category) {
            if (($category['deduction_mode'] ?? '') === '') {
                $categories[$index]['deduction_mode'] = null;
            }

            if (($category['deduction_value'] ?? '') === '') {
                $categories[$index]['deduction_value'] = null;
            }

            if (($category['bank_id'] ?? '') === '') {
                $categories[$index]['bank_id'] = null;
            }
        }

        $this->merge(['categories' => $categories]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('categories', []) as $index => $category) {
                $type = $category['allocation_type'] ?? null;

                if ($type === CategoryAllocationType::Percentage->value && ! isset($category['percentage'])) {
                    $validator->errors()->add("categories.{$index}.percentage", __('Percentage is required.'));
                }

                if ($type === CategoryAllocationType::Deduction->value && ! isset($category['deduct_from_index'])) {
                    $validator->errors()->add("categories.{$index}.deduct_from_index", __('Source category is required.'));
                }
            }
        });
    }
}
