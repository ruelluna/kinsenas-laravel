<?php

namespace App\Services\Billing;

use App\Enums\BillingMode;
use App\Models\SubscriptionPlan;

class BillingPlanPresenter
{
    /**
     * @return array{
     *     launchDiscountPercent: int
     * }|null
     */
    public function openBetaOffer(): ?array
    {
        if (! BillingMode::isOpenBeta()) {
            return null;
        }

        return [
            'launchDiscountPercent' => (int) config('billing.open_beta.launch_discount_percent', 20),
        ];
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     trialDays: int,
     *     prices: list<array{
     *         id: string,
     *         interval: string,
     *         intervalLabel: string,
     *         amount: int,
     *         currency: string
     *     }>
     * }|null
     */
    public function trialOffer(): ?array
    {
        $plan = $this->defaultPlan();

        if ($plan === null) {
            return null;
        }

        return $this->presentPlan($plan);
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     trialDays: int,
     *     prices: list<array{
     *         id: string,
     *         interval: string,
     *         intervalLabel: string,
     *         amount: int,
     *         currency: string
     *     }>
     * }>
     */
    public function activePlans(): array
    {
        return SubscriptionPlan::query()
            ->where('is_active', true)
            ->with('prices')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPlan $plan) => $this->presentPlan($plan))
            ->all();
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     trialDays: int,
     *     prices: list<array{
     *         id: string,
     *         interval: string,
     *         intervalLabel: string,
     *         amount: int,
     *         currency: string
     *     }>
     * }
     */
    public function presentPlan(SubscriptionPlan $plan): array
    {
        $plan->loadMissing('prices');

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'trialDays' => $plan->trial_days,
            'prices' => $this->presentPrices($plan),
        ];
    }

    private function defaultPlan(): ?SubscriptionPlan
    {
        return SubscriptionPlan::query()
            ->where('slug', config('billing.default_plan_slug'))
            ->where('is_active', true)
            ->with('prices')
            ->first()
            ?? SubscriptionPlan::query()
                ->where('is_active', true)
                ->with('prices')
                ->orderBy('sort_order')
                ->first();
    }

    /**
     * @return list<array{
     *     id: string,
     *     interval: string,
     *     intervalLabel: string,
     *     amount: int,
     *     currency: string
     * }>
     */
    private function presentPrices(SubscriptionPlan $plan): array
    {
        return $plan->prices
            ->where('is_active', true)
            ->values()
            ->map(fn ($price) => [
                'id' => $price->id,
                'interval' => $price->interval->value,
                'intervalLabel' => $price->interval->label(),
                'amount' => $price->amount,
                'currency' => $price->currency,
            ])
            ->all();
    }
}
