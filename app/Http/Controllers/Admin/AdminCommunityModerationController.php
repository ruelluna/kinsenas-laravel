<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Services\Content\CommunityModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminCommunityModerationController extends Controller
{
    public function __construct(private CommunityModerationService $moderationService) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('admin.content.community.settings');
    }

    public function approve(Request $request, CommunityPost $communityPost): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlatform(), 403);

        $this->moderationService->approve($communityPost, $request->user());

        return back()->with('toast', ['type' => 'success', 'message' => 'Community post approved.']);
    }

    public function reject(Request $request, CommunityPost $communityPost): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlatform(), 403);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->moderationService->reject(
            $communityPost,
            $request->user(),
            $request->string('rejection_reason')->toString(),
        );

        return back()->with('toast', ['type' => 'success', 'message' => 'Community post rejected.']);
    }
}
