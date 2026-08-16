<?php

namespace App\Http\Requests\Admin;

use App\Enums\SubscriptionFeature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionPlanRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('subscription_plans', 'slug')],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', Rule::enum(SubscriptionFeature::class)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['sometimes', 'boolean'],
            'prices.monthly.amount' => ['required', 'integer', 'min:0'],
            'prices.monthly.is_active' => ['sometimes', 'boolean'],
            'prices.yearly.amount' => ['required', 'integer', 'min:0'],
            'prices.yearly.is_active' => ['sometimes', 'boolean'],
        ];
    }
}
