<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPaymentRequest extends FormRequest
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
            'plan_price_id' => ['required', 'uuid', 'exists:subscription_plan_prices,id'],
            'reference_number' => ['required', 'string', 'max:255'],
            'proof_image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
