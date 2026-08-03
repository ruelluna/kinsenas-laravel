<?php

namespace App\Http\Requests\Savings;

use App\Enums\RecipientType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRecipientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $notes = $this->input('notes');

        $this->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'notes' => is_string($notes) ? trim($notes) ?: null : $notes,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(RecipientType::class)],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
