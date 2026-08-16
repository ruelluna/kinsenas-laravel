<?php

namespace App\Http\Requests\Admin;

use App\Enums\BillingInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivateSubscriptionRequest extends FormRequest
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
            'interval' => ['required', 'string', Rule::enum(BillingInterval::class)],
            'plan_id' => ['nullable', 'uuid', Rule::exists('subscription_plans', 'id')->where('is_active', true)],
        ];
    }
}
