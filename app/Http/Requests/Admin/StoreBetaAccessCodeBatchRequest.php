<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBetaAccessCodeBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManagePlatform() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
