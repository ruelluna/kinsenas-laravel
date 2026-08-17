<?php

namespace App\Services\Content;

use App\Enums\CommunityReportReason;
use App\Enums\CommunityReportStatus;
use App\Models\CommunityPost;
use App\Models\CommunityPostReport;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CommunityReportService
{
    /**
     * @param  array{reason: string, details?: string|null}  $data
     */
    public function report(CommunityPost $post, User $reporter, array $data): CommunityPostReport
    {
        if ($post->isOwnedBy($reporter)) {
            throw ValidationException::withMessages([
                'reason' => __('You cannot report your own post.'),
            ]);
        }

        $existing = CommunityPostReport::query()
            ->where('community_post_id', $post->id)
            ->where('reporter_id', $reporter->id)
            ->where('status', CommunityReportStatus::Open)
            ->first();

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'reason' => __('You already reported this post.'),
            ]);
        }

        return CommunityPostReport::query()->create([
            'community_post_id' => $post->id,
            'reporter_id' => $reporter->id,
            'reason' => CommunityReportReason::from($data['reason']),
            'details' => $data['details'] ?? null,
            'status' => CommunityReportStatus::Open,
        ]);
    }

    public function dismiss(CommunityPostReport $report, User $admin): CommunityPostReport
    {
        $report->update([
            'status' => CommunityReportStatus::Dismissed,
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);

        return $report->fresh(['post', 'reporter']);
    }

    public function resolve(CommunityPostReport $report, User $admin): CommunityPostReport
    {
        $report->update([
            'status' => CommunityReportStatus::Resolved,
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);

        return $report->fresh(['post', 'reporter']);
    }
}
