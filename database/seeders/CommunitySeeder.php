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
        $categories = [
            [
                'slug' => 'payday-wins',
                'name' => 'Payday wins',
                'description' => 'Stories about saving, splitting, or stretching a paycheck.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'side-hustle-stories',
                'name' => 'Side hustle stories',
                'description' => 'How members started and grew extra income streams.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'saving-milestones',
                'name' => 'Saving milestones',
                'description' => 'First emergency fund, paid-off debt, and other money milestones.',
                'sort_order' => 3,
            ],
            [
                'slug' => 'tips-lessons',
                'name' => 'Tips & lessons',
                'description' => 'Quick tips and lessons learned from real life.',
                'sort_order' => 4,
            ],
            [
                'slug' => 'family-household',
                'name' => 'Family & household',
                'description' => 'Padala, shared bills, and household money decisions.',
                'sort_order' => 5,
            ],
            [
                'slug' => 'questions-advice',
                'name' => 'Questions & advice',
                'description' => 'Ask the community or share what worked for you.',
                'sort_order' => 6,
            ],
        ];

        $seededCategories = [];

        foreach ($categories as $category) {
            $seededCategories[$category['slug']] = CommunityCategory::query()->firstOrCreate(
                ['slug' => $category['slug']],
                [
                    ...$category,
                    'status' => ContentPostStatus::Published,
                ],
            );
        }

        $author = User::query()->firstOrCreate(
            ['email' => 'community-member@kinsenas.test'],
            [
                'name' => 'Community Member',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ],
        );

        $savedBonus = CommunityPost::query()->updateOrCreate(
            ['slug' => 'saved-half-my-bonus'],
            [
                'user_id' => $author->id,
                'title' => 'Saved half my bonus',
                'excerpt' => 'Split my 13th month pay across emergency and travel funds.',
                'body' => '<p>I moved half to my emergency fund and half to a travel sinking fund before spending anything.</p>',
                'status' => CommunityPostStatus::Published,
                'published_at' => now()->subDays(3),
            ],
        );
        $savedBonus->categories()->sync([
            $seededCategories['payday-wins']->id,
            $seededCategories['saving-milestones']->id,
        ]);

        $baking = CommunityPost::query()->updateOrCreate(
            ['slug' => 'weekend-baking-orders'],
            [
                'user_id' => $author->id,
                'title' => 'Weekend baking orders',
                'excerpt' => 'Started with three neighbors and now take prepaid orders every Saturday.',
                'body' => '<p>I posted in our barangay group, set a simple menu, and tracked costs in a spreadsheet.</p>',
                'status' => CommunityPostStatus::Published,
                'published_at' => now()->subDay(),
            ],
        );
        $baking->categories()->sync([
            $seededCategories['side-hustle-stories']->id,
            $seededCategories['tips-lessons']->id,
        ]);

        $pending = CommunityPost::query()->updateOrCreate(
            ['slug' => 'pending-review-story'],
            [
                'user_id' => $author->id,
                'title' => 'Pending review story',
                'excerpt' => 'Waiting for moderator approval.',
                'body' => '<p>This post should appear in the admin moderation queue.</p>',
                'status' => CommunityPostStatus::Pending,
                'published_at' => null,
            ],
        );
        $pending->categories()->sync([$seededCategories['payday-wins']->id]);
    }
}
