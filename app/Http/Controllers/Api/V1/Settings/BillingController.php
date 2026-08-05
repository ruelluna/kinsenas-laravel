<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethodConfig;
use App\Services\Billing\BillingPlanPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function show(Request $request, BillingPlanPresenter $billingPlanPresenter): JsonResponse
    {
        $user = $request->user();
        $team = $user->currentTeam?->load('subscription.plan');

        abort_if($team === null, 404);

        $subscription = $team->subscription;
        $paymentMethod = PaymentMethodConfig::query()->where('is_active', true)->first();

        return response()->json([
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'isPersonal' => $team->is_personal,
            ],
            'canManageBilling' => $user->canManageBilling($team),
            'subscription' => $subscription ? [
                'status' => $subscription->status->value,
                'statusLabel' => $subscription->status->label(),
                'trialEndsAt' => $subscription->trial_ends_at?->toISOString(),
                'currentPeriodEndsAt' => $subscription->current_period_ends_at?->toISOString(),
                'planName' => $subscription->plan?->name,
            ] : null,
            'plans' => $billingPlanPresenter->activePlans(),
            'paymentMethod' => $paymentMethod ? [
                'label' => $paymentMethod->label,
                'instructions' => $paymentMethod->instructions,
                'qrImageUrl' => $paymentMethod->qr_image_path ? asset('storage/'.$paymentMethod->qr_image_path) : null,
            ] : null,
        ]);
    }
}
