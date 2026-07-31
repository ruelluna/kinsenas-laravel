<?php

namespace App\Http\Requests\Savings;

use Illuminate\Foundation\Http\FormRequest;

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
            'categories.*.name' => ['required', 'string', 'max:255'],
            'categories.*.percentage' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'is_shared_with_team' => ['sometimes', 'boolean'],
        ];
    }
}
