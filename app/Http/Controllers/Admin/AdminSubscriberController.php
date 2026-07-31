<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BillingInterval;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivateSubscriptionRequest;
use App\Http\Requests\Admin\CancelSubscriptionRequest;
use App\Http\Requests\Admin\ChangePlanRequest;
use App\Http\Requests\Admin\ExtendTrialRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSubscriberController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $users = User::query()
            ->with(['subscription.plan'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status === 'none', fn ($query) => $query->whereDoesntHave('subscription'))
            ->when($status && $status !== 'none', function ($query) use ($status) {
                $query->whereHas('subscription', fn ($q) => $q->where('status', $status));
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/subscribers/index', [
            'subscribers' => $users->through(fn (User $user) => $this->mapSubscriber($user)),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'statusOptions' => [
                ['value' => '', 'label' => 'All'],
                ['value' => SubscriptionStatus::Trialing->value, 'label' => 'Trialing'],
                ['value' => SubscriptionStatus::Active->value, 'label' => 'Active'],
                ['value' => SubscriptionStatus::PastDue->value, 'label' => 'Past Due'],
                ['value' => SubscriptionStatus::Cancelled->value, 'label' => 'Cancelled'],
                ['value' => 'none', 'label' => 'No subscription'],
            ],
        ]);
    }

    public function show(User $user): Response
    {
        $user->load(['subscription.plan', 'paymentSubmissions.planPrice.plan']);

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
            ]);

        return Inertia::render('admin/subscribers/show', [
            'subscriber' => $this->mapSubscriber($user),
            'paymentSubmissions' => $user->paymentSubmissions
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($submission) => [
                    'id' => $submission->id,
                    'referenceNumber' => $submission->reference_number,
                    'status' => $submission->status->value,
                    'planName' => $submission->planPrice?->plan?->name,
                    'interval' => $submission->planPrice?->interval?->label(),
                    'amount' => $submission->planPrice?->amount,
                    'createdAt' => $submission->created_at->toISOString(),
                    'notes' => $submission->notes,
                ]),
            'plans' => $plans,
            'intervalOptions' => array_map(
                fn (BillingInterval $interval) => [
                    'value' => $interval->value,
                    'label' => $interval->label(),
                ],
                BillingInterval::cases(),
            ),
        ]);
    }

    public function extendTrial(ExtendTrialRequest $request, User $user): RedirectResponse
    {
        $subscription = $user->subscription()->firstOrFail();

        $this->subscriptionService->extendTrial($subscription, $request->validated('days'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trial extended.')]);

        return back();
    }

    public function cancel(CancelSubscriptionRequest $request, User $user): RedirectResponse
    {
        $subscription = $user->subscription()->firstOrFail();

        $this->subscriptionService->cancel($subscription, $request->validated('reason'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Subscription cancelled.')]);

        return back();
    }

    public function activate(ActivateSubscriptionRequest $request, User $user): RedirectResponse
    {
        if ($user->subscription === null) {
            $this->subscriptionService->startTrial($user);
            $user->refresh();
        }

        $validated = $request->validated();
        $plan = isset($validated['plan_id'])
            ? SubscriptionPlan::query()->findOrFail($validated['plan_id'])
            : null;

        $this->subscriptionService->activateManually(
            $user,
            BillingInterval::from($validated['interval']),
            $plan,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Subscription activated.')]);

        return back();
    }

    public function changePlan(ChangePlanRequest $request, User $user): RedirectResponse
    {
        $subscription = $user->subscription()->firstOrFail();
        $plan = SubscriptionPlan::query()->findOrFail($request->validated('plan_id'));

        $this->subscriptionService->changePlan($subscription, $plan);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan changed.')]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSubscriber(User $user): array
    {
        $subscription = $user->subscription;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'isPlatformAdmin' => $user->isPlatformAdmin(),
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status->value,
                'statusLabel' => $subscription->status->label(),
                'planName' => $subscription->plan?->name,
                'planId' => $subscription->plan_id,
                'trialEndsAt' => $subscription->trial_ends_at?->toISOString(),
                'currentPeriodEndsAt' => $subscription->current_period_ends_at?->toISOString(),
                'hasAccess' => $this->subscriptionService->userHasAccess($user),
            ] : null,
            'createdAt' => $user->created_at->toISOString(),
        ];
    }
}
