<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPostReport;
use App\Services\Content\CommunityReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminCommunityReportController extends Controller
{
    public function __construct(private CommunityReportService $reportService) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('admin.content.community.settings');
    }

    public function dismiss(Request $request, CommunityPostReport $communityPostReport): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlatform(), 403);

        $this->reportService->dismiss($communityPostReport, $request->user());

        return back()->with('toast', ['type' => 'success', 'message' => 'Report dismissed.']);
    }

    public function resolve(Request $request, CommunityPostReport $communityPostReport): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlatform(), 403);

        $this->reportService->resolve($communityPostReport, $request->user());

        return back()->with('toast', ['type' => 'success', 'message' => 'Report resolved.']);
    }
}
