<?php

namespace Database\Factories;

use App\Enums\CommunityReportReason;
use App\Enums\CommunityReportStatus;
use App\Models\CommunityPost;
use App\Models\CommunityPostReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityPostReport>
 */
class CommunityPostReportFactory extends Factory
{
    protected $model = CommunityPostReport::class;

    public function definition(): array
    {
        return [
            'community_post_id' => CommunityPost::factory()->published(),
            'reporter_id' => User::factory(),
            'reason' => CommunityReportReason::Spam,
            'details' => fake()->optional()->sentence(),
            'status' => CommunityReportStatus::Open,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }
}
