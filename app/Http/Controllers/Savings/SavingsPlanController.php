<?php

namespace App\Http\Controllers\Savings;

use App\Enums\TeamPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveSavingsPlanRequest;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlanPageGuidance;
use App\Models\Team;
use App\Services\Marketing\ActivationGhlTagService;
use App\Services\Savings\BankPayloadMapper;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavingsPlanController extends Controller
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $fundBalanceService,
        private ActivationGhlTagService $activationGhlTagService,
    ) {}

    public function show(Request $request, Team $current_team): Response
    {
        $user = $request->user();
        $plan = $this->planService->forTeam($current_team, $user);
        $templates = SavingsFormulaTemplate::query()->with('categories')->orderBy('name')->get();
        $pageGuidance = SavingsPlanPageGuidance::instance();

        $teamBanks = $current_team->banks()
            ->where('is_active', true)
            ->with('institution')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($bank) => BankPayloadMapper::toOption($bank));

        return Inertia::render('savings/plan', [
            'pageGuidance' => [
                'chooserIntro' => $pageGuidance->chooser_intro,
                'chooserVideoUrl' => $pageGuidance->chooser_video_url,
                'beforeChooseNote' => $pageGuidance->before_choose_note,
                'afterIncomeRules' => $pageGuidance->after_income_rules,
                'afterIncomeVideoUrl' => $pageGuidance->after_income_video_url,
            ],
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'currency' => $plan->currency,
                'isSharedWithTeam' => $plan->is_shared_with_team,
                'allowEditingSpends' => $plan->allow_editing_spends,
                'categories' => $plan->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'allocationType' => $c->allocation_type->value,
                    'percentage' => $c->percentage !== null ? (string) $c->percentage : null,
                    'deductionMode' => $c->deduction_mode?->value,
                    'deductionValue' => $c->deduction_value !== null ? (string) $c->deduction_value : null,
                    'deductFromCategoryId' => $c->deduct_from_category_id,
                    'deductFromCategoryName' => $c->deductFromCategory?->name,
                    'bankId' => $c->bank_id,
                ]),
                'hasLockedIncome' => $plan->hasLockedIncomePeriod(),
                'hasIncome' => $plan->hasIncomePeriod(),
                'percentagesLocked' => $plan->hasIncomePeriod(),
            ] : null,
            'fundBalances' => $plan && $plan->hasLockedIncomePeriod()
                ? $this->fundBalanceService->balancesForPlan($plan)
                : [],
            'templates' => $templates->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'description' => $t->description,
                'bestFor' => $t->best_for,
                'videoEmbedUrl' => $t->video_embed_url,
                'categories' => $t->categories->map(fn ($c) => [
                    'name' => $c->name,
                    'percentage' => (string) $c->percentage,
                    'description' => $c->description,
                ]),
            ]),
            'teamBanks' => $teamBanks,
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

        $this->activationGhlTagService->syncPlanCreated(
            $request->user(),
            $current_team,
            $template->slug,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Savings plan created.')]);

        return back();
    }

    public function storeCustom(Request $request, Team $current_team): RedirectResponse
    {
        $this->planService->createCustom($current_team, $request->user());

        $this->activationGhlTagService->syncPlanCreated(
            $request->user(),
            $current_team,
            'custom',
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom savings plan created.')]);

        return back();
    }

    public function update(SaveSavingsPlanRequest $request, Team $current_team): RedirectResponse
    {
        $plan = $this->planService->forTeam($current_team, $request->user());

        abort_if($plan === null, 404);

        if ($plan->created_by_user_id !== $request->user()->id && ! ($plan->is_shared_with_team && $request->user()->hasTeamPermission($current_team, TeamPermission::UpdateTeam))) {
            abort(403);
        }

        $this->planService->updateCategories($plan, $request->validated('categories'));

        if ($request->has('is_shared_with_team')) {
            $this->planService->updateShareSetting($plan, $request->boolean('is_shared_with_team'));
        }

        if ($request->has('allow_editing_spends')) {
            $this->planService->updateSpendingEditSetting($plan, $request->boolean('allow_editing_spends'));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Savings plan updated.')]);

        return back();
    }
}
