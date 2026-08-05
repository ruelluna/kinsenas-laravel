<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Enums\BetaFeedbackCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreBetaFeedbackRequest;
use App\Models\BetaFeedback;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BetaFeedbackController extends Controller
{
    public function __construct(private GhlUserTagService $ghlUserTagService) {}

    public function create(Request $request): JsonResponse
    {
        return response()->json([
            'categories' => BetaFeedbackCategory::options(),
        ]);
    }

    public function store(StoreBetaFeedbackRequest $request): JsonResponse
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

        return response()->json([
            'message' => __('Thanks for your feedback!'),
            'feedback' => [
                'id' => $feedback->id,
                'category' => $feedback->category?->value,
                'createdAt' => $feedback->created_at?->toISOString(),
            ],
        ], 201);
    }
}
