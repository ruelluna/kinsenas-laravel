<?php

namespace App\Http\Requests\Savings;

use App\Enums\CategoryAllocationType;
use App\Enums\DeductionMode;
use App\Models\SavingsPlan;
use App\Services\Savings\SavingsPlanService;
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
            'categories.*.opening_balance' => ['nullable', 'numeric', 'min:0'],
            'is_shared_with_team' => ['sometimes', 'boolean'],
            'allow_editing_spends' => ['sometimes', 'boolean'],
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

            if (array_key_exists('opening_balance', $category) && ($category['opening_balance'] ?? '') === '') {
                $categories[$index]['opening_balance'] = null;
            }
        }

        $this->merge(['categories' => $categories]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $team = $this->route('current_team');
            $plan = $team !== null
                ? app(SavingsPlanService::class)->forTeam($team, $this->user())
                : null;

            foreach ($this->input('categories', []) as $index => $category) {
                $type = $category['allocation_type'] ?? null;

                if ($type === CategoryAllocationType::Percentage->value && ! isset($category['percentage'])) {
                    $validator->errors()->add("categories.{$index}.percentage", __('Percentage is required.'));
                }

                if ($type === CategoryAllocationType::Deduction->value && ! isset($category['deduct_from_index'])) {
                    $validator->errors()->add("categories.{$index}.deduct_from_index", __('Source fund bucket is required.'));
                }

                if ($plan instanceof SavingsPlan && $plan->hasIncomePeriod() && array_key_exists('opening_balance', $category)) {
                    $existing = $plan->categories->firstWhere('id', $category['id'] ?? null);
                    $submitted = $category['opening_balance'] ?? null;
                    $existingPlain = $existing?->opening_balance_encrypted;

                    $submittedNormalized = $submitted === null || $submitted === ''
                        ? null
                        : number_format((float) $submitted, 2, '.', '');
                    $existingNormalized = $existingPlain === null || $existingPlain === ''
                        ? null
                        : number_format((float) $existingPlain, 2, '.', '');

                    if ($submittedNormalized !== $existingNormalized) {
                        $validator->errors()->add(
                            "categories.{$index}.opening_balance",
                            __('Existing savings cannot be changed after your first income entry.'),
                        );
                    }
                }
            }
        });
    }
}
