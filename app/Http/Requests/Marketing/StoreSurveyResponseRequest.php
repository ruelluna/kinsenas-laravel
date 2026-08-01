<?php

namespace App\Http\Requests\Marketing;

use App\Enums\SurveyLanguage;
use App\Enums\SurveyResultSlug;
use App\Support\Survey\SurveyAnswerOptions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSurveyResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('completedAt') && ! $this->has('completed_at')) {
            $this->merge([
                'completed_at' => $this->input('completedAt'),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'language' => ['required', 'string', Rule::enum(SurveyLanguage::class)],
            'email' => ['required', 'string', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'result' => ['required', 'string', Rule::enum(SurveyResultSlug::class)],
            'completed_at' => ['required', 'date'],
            'answers' => ['required', 'array'],
        ];

        foreach (SurveyAnswerOptions::SINGLE_SELECT as $questionId => $allowedValues) {
            $rules["answers.{$questionId}"] = ['required', 'string', Rule::in($allowedValues)];
        }

        foreach (SurveyAnswerOptions::MULTI_SELECT as $questionId => $allowedValues) {
            if ($questionId === 'q8') {
                $rules["answers.{$questionId}"] = ['nullable', 'array'];
                $rules["answers.{$questionId}.*"] = ['string', Rule::in($allowedValues)];
            } else {
                $rules["answers.{$questionId}"] = ['required', 'array', 'min:1'];
                $rules["answers.{$questionId}.*"] = ['string', Rule::in($allowedValues)];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('Please enter your email address.'),
            'email.email' => __('Please enter a valid email address.'),
            'answers.required' => __('Survey answers are required.'),
        ];
    }
}
