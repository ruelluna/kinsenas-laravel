<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BillingInterval;
use App\Enums\SubscriptionFeature;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionPlanRequest;
use App\Http\Requests\Admin\UpdateSubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminSubscriptionPlanController extends Controller
{
    public function index(): Response
    {
        $plans = SubscriptionPlan::query()->with('prices')->orderBy('sort_order')->get();

        return Inertia::render('admin/plans/index', [
            'plans' => $plans->map(fn ($plan) => $this->mapPlan($plan)),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/plans/create', [
            'features' => $this->featureOptions(),
        ]);
    }

    public function store(StoreSubscriptionPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $plan = SubscriptionPlan::query()->create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'trial_days' => $validated['trial_days'],
                'features' => $validated['features'] ?? [],
                'sort_order' => $validated['sort_order'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->syncPrices($plan, $validated['prices']);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan created.')]);

        return to_route('admin.plans.index');
    }

    public function edit(SubscriptionPlan $plan): Response
    {
        $plan->load('prices');

        return Inertia::render('admin/plans/edit', [
            'plan' => $this->mapPlan($plan),
            'features' => $this->featureOptions(),
        ]);
    }

    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($plan, $validated) {
            $plan->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'trial_days' => $validated['trial_days'],
                'features' => $validated['features'] ?? [],
                'sort_order' => $validated['sort_order'],
                'is_active' => $validated['is_active'] ?? $plan->is_active,
            ]);

            $this->syncPrices($plan, $validated['prices']);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan updated.')]);

        return to_route('admin.plans.index');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function featureOptions(): array
    {
        return array_map(
            fn (SubscriptionFeature $feature) => [
                'value' => $feature->value,
                'label' => $feature->label(),
            ],
            SubscriptionFeature::cases(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPlan(SubscriptionPlan $plan): array
    {
        $monthly = $plan->prices->firstWhere('interval', BillingInterval::Monthly);
        $yearly = $plan->prices->firstWhere('interval', BillingInterval::Yearly);

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'trialDays' => $plan->trial_days,
            'features' => $plan->features ?? [],
            'isActive' => $plan->is_active,
            'sortOrder' => $plan->sort_order,
            'prices' => $plan->prices->map(fn ($p) => [
                'id' => $p->id,
                'interval' => $p->interval->value,
                'amount' => $p->amount,
                'currency' => $p->currency,
                'isActive' => $p->is_active,
            ]),
            'monthlyAmount' => $monthly?->amount,
            'yearlyAmount' => $yearly?->amount,
            'monthlyActive' => $monthly?->is_active ?? true,
            'yearlyActive' => $yearly?->is_active ?? true,
        ];
    }

    /**
     * @param  array<string, array{amount: int, is_active?: bool}>  $prices
     */
    private function syncPrices(SubscriptionPlan $plan, array $prices): void
    {
        foreach (BillingInterval::cases() as $interval) {
            $key = $interval->value;
            $data = $prices[$key] ?? null;

            if ($data === null) {
                continue;
            }

            $plan->prices()->updateOrCreate(
                ['interval' => $interval],
                [
                    'amount' => $data['amount'],
                    'currency' => config('billing.currency', 'PHP'),
                    'is_active' => $data['is_active'] ?? true,
                ],
            );
        }
    }
}
