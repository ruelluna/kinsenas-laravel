<?php

namespace App\Http\Requests\Settings;

use App\Enums\BetaFeedbackCategory;
use App\Enums\BillingMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBetaFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return BillingMode::isOpenBeta() && $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $category = $this->input('category');

        if (! is_string($category) || $category === '') {
            $this->merge(['category' => BetaFeedbackCategory::General->value]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', Rule::enum(BetaFeedbackCategory::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => __('Please share your feedback before submitting.'),
            'message.max' => __('Feedback may not be longer than :max characters.'),
        ];
    }
}
