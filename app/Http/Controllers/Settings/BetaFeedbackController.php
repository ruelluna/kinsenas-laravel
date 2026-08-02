<?php

namespace App\Http\Controllers\Settings;

use App\Enums\BetaFeedbackCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreBetaFeedbackRequest;
use App\Models\BetaFeedback;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BetaFeedbackController extends Controller
{
    public function __construct(private GhlUserTagService $ghlUserTagService) {}

    public function create(Request $request): Response
    {
        return Inertia::render('settings/feedback', [
            'categories' => BetaFeedbackCategory::options(),
        ]);
    }

    public function store(StoreBetaFeedbackRequest $request): RedirectResponse
    {
        $user = $request->user();
        $team = $user->currentTeam;

        $feedback = BetaFeedback::query()->create([
            'user_id' => $user->id,
            'team_id' => $team?->id,
            'message' => $request->validated('message'),
            'category' => $request->validated('category'),
        ]);

        Log::info('Beta feedback submitted', [
            'feedback_id' => $feedback->id,
            'user_id' => $user->id,
            'team_id' => $team?->id,
            'category' => $feedback->category?->value,
        ]);

        $category = $feedback->category ?? BetaFeedbackCategory::General;

        $this->ghlUserTagService->dispatch(
            $user,
            [
                GhlTagCatalog::BETA_FEEDBACK,
                GhlTagCatalog::betaFeedbackCategoryTag($category->value),
            ],
            [],
            ['event' => 'beta_feedback_submitted', 'feedback_id' => $feedback->id],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Thanks for your feedback!')]);

        return to_route('settings.feedback');
    }
}
