<?php

namespace Database\Factories;

use App\Enums\ContentEngagementEventType;
use App\Enums\ContentEngagementSource;
use App\Models\ContentEngagementEvent;
use App\Models\ContentPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentEngagementEvent>
 */
class ContentEngagementEventFactory extends Factory
{
    protected $model = ContentEngagementEvent::class;

    public function definition(): array
    {
        return [
            'content_post_id' => ContentPost::factory(),
            'user_id' => User::factory(),
            'event_type' => ContentEngagementEventType::Viewed,
            'source' => ContentEngagementSource::Internal,
            'metadata' => null,
        ];
    }
}
