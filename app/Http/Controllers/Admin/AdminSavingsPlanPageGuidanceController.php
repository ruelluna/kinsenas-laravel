<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSavingsPlanPageGuidanceRequest;
use App\Models\SavingsPlanPageGuidance;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminSavingsPlanPageGuidanceController extends Controller
{
    public function edit(): Response
    {
        $guidance = SavingsPlanPageGuidance::instance();

        return Inertia::render('admin/savings-plan-guidance/edit', [
            'guidance' => [
                'chooserIntro' => $guidance->chooser_intro,
                'chooserVideoUrl' => $guidance->chooser_video_url,
                'beforeChooseNote' => $guidance->before_choose_note,
                'afterIncomeRules' => $guidance->after_income_rules,
                'afterIncomeVideoUrl' => $guidance->after_income_video_url,
            ],
        ]);
    }

    public function update(UpdateSavingsPlanPageGuidanceRequest $request): RedirectResponse
    {
        $guidance = SavingsPlanPageGuidance::instance();

        $guidance->update([
            'chooser_intro' => $request->validated('chooser_intro'),
            'chooser_video_url' => $request->validated('chooser_video_url'),
            'before_choose_note' => $request->validated('before_choose_note'),
            'after_income_rules' => $request->validated('after_income_rules'),
            'after_income_video_url' => $request->validated('after_income_video_url'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Savings plan guidance updated.')]);

        return back();
    }
}
