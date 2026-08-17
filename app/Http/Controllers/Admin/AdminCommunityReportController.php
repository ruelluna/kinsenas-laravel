<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPostReport;
use App\Services\Content\CommunityReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCommunityReportController extends Controller
{
    public function __construct(private CommunityReportService $reportService) {}

    public function index(): Response
    {
        $reports = CommunityPostReport::query()
            ->with(['post', 'reporter'])
            ->where('status', 'open')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (CommunityPostReport $report) => [
                'id' => $report->id,
                'reason' => $report->reason->value,
                'reasonLabel' => $report->reason->label(),
                'details' => $report->details,
                'postTitle' => $report->post?->title,
                'postSlug' => $report->post?->slug,
                'reporterName' => $report->reporter?->name,
                'createdAt' => $report->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/content/community-reports/index', [
            'reports' => $reports,
        ]);
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
