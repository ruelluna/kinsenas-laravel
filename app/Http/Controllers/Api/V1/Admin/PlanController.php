<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'isActive' => $plan->is_active,
                'sortOrder' => $plan->sort_order,
            ]);

        return response()->json(['plans' => $plans]);
    }
}
