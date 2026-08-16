<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\ContentPost;
use App\Services\Content\ContentEngagementService;
use Illuminate\Http\JsonResponse;

class LearnPostReactionController extends Controller
{
    public function __construct(private ContentEngagementService $engagementService) {}

    public function store(ContentPost $post): JsonResponse
    {
        $user = request()->user();

        abort_if($user === null, 403);

        $result = $this->engagementService->toggleHelpfulReaction($post, $user);

        return response()->json($result);
    }
}
