<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendTestPushRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
            'actionUrl' => ['required', 'string', 'max:255', 'starts_with:/'],
            'target' => ['required', 'string', Rule::in(['self', 'email', 'all'])],
            'targetEmail' => ['nullable', 'required_if:target,email', 'email'],
        ];
    }
}
