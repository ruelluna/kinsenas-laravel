<?php

namespace Database\Seeders;

use App\Enums\CommunityPostStatus;
use App\Enums\ContentPostStatus;
use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $paydayWins = CommunityCategory::query()->firstOrCreate(
            ['slug' => 'payday-wins'],
            [
                'name' => 'Payday wins',
                'description' => 'Stories about saving, splitting, or stretching a paycheck.',
                'status' => ContentPostStatus::Published,
                'sort_order' => 1,
            ],
        );

        $sideHustleStories = CommunityCategory::query()->firstOrCreate(
            ['slug' => 'side-hustle-stories'],
            [
                'name' => 'Side hustle stories',
                'description' => 'How members started and grew extra income streams.',
                'status' => ContentPostStatus::Published,
                'sort_order' => 2,
            ],
        );

        CommunityCategory::query()->firstOrCreate(
            ['slug' => 'tips'],
            [
                'name' => 'Tips',
                'description' => 'Quick tips and lessons learned from the community.',
                'status' => ContentPostStatus::Published,
                'sort_order' => 3,
            ],
        );

        $author = User::query()->firstOrCreate(
            ['email' => 'community-member@kinsenas.test'],
            [
                'name' => 'Community Member',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ],
        );

        CommunityPost::query()->updateOrCreate(
            ['slug' => 'saved-half-my-bonus'],
            [
                'community_category_id' => $paydayWins->id,
                'user_id' => $author->id,
                'title' => 'Saved half my bonus',
                'excerpt' => 'Split my 13th month pay across emergency and travel funds.',
                'body' => '<p>I moved half to my emergency fund and half to a travel sinking fund before spending anything.</p>',
                'status' => CommunityPostStatus::Published,
                'published_at' => now()->subDays(3),
            ],
        );

        CommunityPost::query()->updateOrCreate(
            ['slug' => 'weekend-baking-orders'],
            [
                'community_category_id' => $sideHustleStories->id,
                'user_id' => $author->id,
                'title' => 'Weekend baking orders',
                'excerpt' => 'Started with three neighbors and now take prepaid orders every Saturday.',
                'body' => '<p>I posted in our barangay group, set a simple menu, and tracked costs in a spreadsheet.</p>',
                'status' => CommunityPostStatus::Published,
                'published_at' => now()->subDay(),
            ],
        );

        CommunityPost::query()->updateOrCreate(
            ['slug' => 'pending-review-story'],
            [
                'community_category_id' => $paydayWins->id,
                'user_id' => $author->id,
                'title' => 'Pending review story',
                'excerpt' => 'Waiting for moderator approval.',
                'body' => '<p>This post should appear in the admin moderation queue.</p>',
                'status' => CommunityPostStatus::Pending,
                'published_at' => null,
            ],
        );
    }
}
