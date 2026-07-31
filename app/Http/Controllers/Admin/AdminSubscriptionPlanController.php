<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Inertia\Inertia;
use Inertia\Response;

class AdminSubscriptionPlanController extends Controller
{
    public function index(): Response
    {
        $plans = SubscriptionPlan::query()->with('prices')->orderBy('sort_order')->get();

        return Inertia::render('admin/plans/index', [
            'plans' => $plans->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'trialDays' => $plan->trial_days,
                'isActive' => $plan->is_active,
                'prices' => $plan->prices->map(fn ($p) => [
                    'id' => $p->id,
                    'interval' => $p->interval->value,
                    'amount' => $p->amount,
                    'currency' => $p->currency,
                    'isActive' => $p->is_active,
                ]),
            ]),
        ]);
    }
}
