<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveSavingsPlanRequest;
use App\Models\SavingsFormulaTemplate;
use App\Models\Team;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavingsPlanController extends Controller
{
    public function __construct(private SavingsPlanService $planService)
    {
    }

    public function show(Request $request, Team $current_team): Response
    {
        $user = $request->user();
        $plan = $this->planService->forTeam($current_team, $user);
        $templates = SavingsFormulaTemplate::query()->with('categories')->orderBy('name')->get();

        return Inertia::render('savings/plan', [
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'currency' => $plan->currency,
                'isSharedWithTeam' => $plan->is_shared_with_team,
                'categories' => $plan->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'percentage' => (string) $c->percentage,
                ]),
                'hasLockedIncome' => $plan->hasLockedIncomePeriod(),
            ] : null,
            'templates' => $templates->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'description' => $t->description,
                'categories' => $t->categories->map(fn ($c) => [
                    'name' => $c->name,
                    'percentage' => (string) $c->percentage,
                ]),
            ]),
        ]);
    }

    public function storeFromTemplate(Request $request, Team $current_team, SavingsFormulaTemplate $template): RedirectResponse
    {
        $this->planService->cloneFromTemplate(
            $current_team,
            $request->user(),
            $template,
            $template->name,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Savings plan created.')]);

        return back();
    }

    public function update(SaveSavingsPlanRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        abort_if($plan === null, 404);

        if ($plan->created_by_user_id !== $request->user()->id && ! ($plan->is_shared_with_team && $request->user()->hasTeamPermission($current_team, \App\Enums\TeamPermission::UpdateTeam))) {
            abort(403);
        }

        $this->planService->updateCategories($plan, $request->validated('categories'));

        if ($request->has('is_shared_with_team')) {
            $this->planService->updateShareSetting($plan, $request->boolean('is_shared_with_team'));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Savings plan updated.')]);

        return back();
    }
}
