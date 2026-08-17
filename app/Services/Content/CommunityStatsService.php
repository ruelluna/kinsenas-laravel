<?php

namespace App\Services\Content;

use App\Enums\CommunityPostStatus;
use App\Enums\CommunityReportStatus;
use App\Models\CommunityPost;
use App\Models\CommunityPostReport;
use Illuminate\Support\Carbon;

class CommunityStatsService
{
    /**
     * @return array{
     *     total: int,
     *     published: int,
     *     pending: int,
     *     rejected: int,
     *     withdrawn: int,
     *     openReports: int,
     *     recentSubmissions: int
     * }
     */
    public function summary(?int $days = null): array
    {
        $since = $days !== null ? Carbon::now()->subDays($days) : null;

        $recentQuery = CommunityPost::query();
        if ($since !== null) {
            $recentQuery->where('created_at', '>=', $since);
        }

        return [
            'total' => CommunityPost::query()->count(),
            'published' => CommunityPost::query()->where('status', CommunityPostStatus::Published)->count(),
            'pending' => CommunityPost::query()->where('status', CommunityPostStatus::Pending)->count(),
            'rejected' => CommunityPost::query()->where('status', CommunityPostStatus::Rejected)->count(),
            'withdrawn' => CommunityPost::query()->where('status', CommunityPostStatus::Withdrawn)->count(),
            'openReports' => CommunityPostReport::query()->where('status', CommunityReportStatus::Open)->count(),
            'recentSubmissions' => $recentQuery->count(),
        ];
    }
}
