<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethodConfig;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user()->load('subscription.plan.prices');
        $plans = SubscriptionPlan::query()->where('is_active', true)->with('prices')->orderBy('sort_order')->get();
        $paymentMethod = PaymentMethodConfig::query()->where('is_active', true)->first();

        return Inertia::render('settings/billing', [
            'subscription' => $user->subscription ? [
                'status' => $user->subscription->status->value,
                'statusLabel' => $user->subscription->status->label(),
                'trialEndsAt' => $user->subscription->trial_ends_at?->toISOString(),
                'currentPeriodEndsAt' => $user->subscription->current_period_ends_at?->toISOString(),
                'planName' => $user->subscription->plan?->name,
            ] : null,
            'plans' => $plans->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'trialDays' => $plan->trial_days,
                'prices' => $plan->prices->where('is_active', true)->map(fn ($price) => [
                    'id' => $price->id,
                    'interval' => $price->interval->value,
                    'intervalLabel' => $price->interval->label(),
                    'amount' => $price->amount,
                    'currency' => $price->currency,
                ])->values(),
            ]),
            'paymentMethod' => $paymentMethod ? [
                'label' => $paymentMethod->label,
                'instructions' => $paymentMethod->instructions,
                'qrImageUrl' => $paymentMethod->qr_image_path ? asset('storage/'.$paymentMethod->qr_image_path) : null,
            ] : null,
        ]);
    }
}
