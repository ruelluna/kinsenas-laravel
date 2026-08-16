<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentQrRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'qr_image' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
