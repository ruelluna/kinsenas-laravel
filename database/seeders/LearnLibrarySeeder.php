<?php

namespace Database\Seeders;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use App\Enums\SideHustleCapitalTier;
use App\Enums\SideHustleDifficulty;
use App\Models\PodcastEpisode;
use App\Models\PodcastShow;
use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use Illuminate\Database\Seeder;

class LearnLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $food = SideHustleCategory::query()->firstOrCreate(
            ['slug' => 'food-beverage'],
            [
                'name' => 'Food & beverage',
                'description' => 'Street food, home-based food, and small food stalls.',
                'status' => ContentPostStatus::Published,
                'sort_order' => 1,
            ],
        );

        $online = SideHustleCategory::query()->firstOrCreate(
            ['slug' => 'online-work'],
            [
                'name' => 'Online work',
                'description' => 'Remote gigs you can start with a laptop and internet.',
                'status' => ContentPostStatus::Published,
                'sort_order' => 2,
            ],
        );

        SideHustle::query()->updateOrCreate(
            ['slug' => 'street-food-cart'],
            [
                'side_hustle_category_id' => $food->id,
                'title' => 'Street food cart',
                'excerpt' => 'Start with one signature snack and a mobile cart near foot traffic.',
                'body' => '<h2>Getting started</h2><p>Pick one product, test pricing, and reinvest your first profits into better equipment.</p><ul><li>Register with your barangay if required</li><li>Track daily sales in a notebook before scaling</li></ul>',
                'difficulty' => SideHustleDifficulty::Beginner,
                'capital_tier' => SideHustleCapitalTier::Low,
                'startup_capital_min' => 5000,
                'startup_capital_max' => 20000,
                'time_commitment_hours_min' => 10,
                'time_commitment_hours_max' => 25,
                'skills' => ['Cooking', 'Customer service'],
                'equipment' => ['Cart', 'Cooler', 'Utensils'],
                'publish_scope' => ContentPublishScope::Both,
                'status' => ContentPostStatus::Published,
                'published_at' => now()->subDays(2),
                'sort_order' => 1,
            ],
        );

        SideHustle::query()->updateOrCreate(
            ['slug' => 'virtual-assistant'],
            [
                'side_hustle_category_id' => $online->id,
                'title' => 'Virtual assistant (VA)',
                'excerpt' => 'Support small business owners with inbox, calendar, and admin tasks.',
                'body' => '<h2>Skills to build first</h2><p>Start with one niche — e-commerce sellers, coaches, or agencies — and offer a clear weekly package.</p><ol><li>Build a simple portfolio page</li><li>Apply on trusted VA job boards</li><li>Track hours and set aside tax savings each payout</li></ol>',
                'difficulty' => SideHustleDifficulty::Intermediate,
                'capital_tier' => SideHustleCapitalTier::Low,
                'startup_capital_min' => 0,
                'startup_capital_max' => 5000,
                'time_commitment_hours_min' => 10,
                'time_commitment_hours_max' => 30,
                'skills' => ['Email management', 'Spreadsheets', 'English communication'],
                'equipment' => ['Laptop', 'Stable internet'],
                'publish_scope' => ContentPublishScope::Both,
                'status' => ContentPostStatus::Published,
                'published_at' => now()->subDay(),
                'sort_order' => 1,
            ],
        );

        $show = PodcastShow::query()->firstOrCreate(
            ['slug' => 'sweldo-stories'],
            [
                'title' => 'Sweldo Stories',
                'description' => 'Conversations about payday habits, side income, and building margin.',
                'status' => ContentPostStatus::Published,
                'published_at' => now()->subDays(4),
                'sort_order' => 1,
            ],
        );

        PodcastEpisode::query()->updateOrCreate(
            ['slug' => 'first-payday-plan'],
            [
                'podcast_show_id' => $show->id,
                'episode_number' => 1,
                'title' => 'Your first payday plan',
                'excerpt' => 'How to split your first sweldo before spending.',
                'show_notes' => '<p>We cover locking income, moving money to banks, and logging your first expense.</p>',
                'audio_embed_url' => 'https://open.spotify.com/embed/episode/example',
                'duration_minutes' => 28,
                'publish_scope' => ContentPublishScope::Both,
                'status' => ContentPostStatus::Published,
                'published_at' => now()->subDays(3),
            ],
        );

        PodcastEpisode::query()->updateOrCreate(
            ['slug' => 'side-hustle-without-burnout'],
            [
                'podcast_show_id' => $show->id,
                'episode_number' => 2,
                'title' => 'Side hustle without burnout',
                'excerpt' => 'Balancing extra income with rest and family time.',
                'show_notes' => '<p>Episode notes on time blocks, minimum viable offers, and when to pause a hustle.</p>',
                'audio_embed_url' => 'https://open.spotify.com/embed/episode/example-2',
                'duration_minutes' => 32,
                'publish_scope' => ContentPublishScope::Both,
                'status' => ContentPostStatus::Published,
                'published_at' => now()->subDays(1),
            ],
        );
    }
}
