<?php

namespace Database\Seeders;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPostType;
use App\Enums\ContentPublishScope;
use App\Enums\ContentSeriesStatus;
use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->where('is_platform_admin', true)->first()
            ?? User::factory()->create(['is_platform_admin' => true, 'email' => 'content-admin@kinsenas.test']);

        $series = ContentSeries::query()->firstOrCreate(
            ['slug' => 'payday-basics'],
            [
                'title' => 'Payday basics',
                'description' => 'A three-part series on locking income, moving money, and reviewing your plan.',
                'status' => ContentSeriesStatus::Published,
                'published_at' => now()->subDays(3),
                'sort_order' => 1,
            ],
        );

        $episodes = [
            [
                'episode_number' => 1,
                'title' => 'Why lock your income?',
                'slug' => 'why-lock-your-income',
                'excerpt' => 'Locking income tells Kinsenas your paycheck is real — so fund balances stay honest.',
                'body' => "## Why lock?\n\nWhen you record income, Kinsenas splits it across your funds. **Locking** confirms you are ready to act on that split.\n\n- Plan stays stable for the pay period\n- Reports reflect what you committed to\n- Transfers align with your formula",
            ],
            [
                'episode_number' => 2,
                'title' => 'Move money to your banks',
                'slug' => 'move-money-to-banks',
                'excerpt' => 'After locking, transfer each fund slice to the bank you assigned — Kinsenas tracks what you planned.',
                'body' => "## Transfers match your plan\n\nUse the **Transfers** screen to move from each fund to its bank account.\n\n1. Open Transfers\n2. Pick source fund and destination bank\n3. Confirm when done in your banking app",
            ],
            [
                'episode_number' => 3,
                'title' => 'Review fund health',
                'slug' => 'review-fund-health',
                'excerpt' => 'A quick payday ritual: check low balances and log your first spend.',
                'body' => "## Stay on track\n\nBefore the next sweldo, glance at **Dashboard** and **Reports**.\n\n- Funds above 90% used need attention\n- Log spending as you go — do not wait until month end",
            ],
        ];

        foreach ($episodes as $episode) {
            ContentPost::query()->updateOrCreate(
                ['slug' => $episode['slug']],
                [
                    ...$episode,
                    'content_series_id' => $series->id,
                    'content_type' => ContentPostType::Episode,
                    'publish_scope' => ContentPublishScope::Both,
                    'status' => ContentPostStatus::Published,
                    'author_id' => $author->id,
                    'published_at' => now()->subDays(4 - $episode['episode_number']),
                    'reading_time_minutes' => 2,
                ],
            );
        }

        ContentPost::query()->updateOrCreate(
            ['slug' => 'payday-reminder-log-first-spend'],
            [
                'title' => 'Log your first spend',
                'excerpt' => 'After transferring, record one real purchase — it keeps Everyday Fund honest.',
                'body' => "Pick the **smallest real expense** after payday and log it under Spending.\n\nThat single entry builds the habit of matching your plan to real life.",
                'content_type' => ContentPostType::Reminder,
                'publish_scope' => ContentPublishScope::Internal,
                'status' => ContentPostStatus::Published,
                'author_id' => $author->id,
                'published_at' => now()->subDay(),
                'reading_time_minutes' => 1,
            ],
        );

        ContentPost::query()->updateOrCreate(
            ['slug' => 'payday-reminder-family-support'],
            [
                'title' => 'Set aside family support early',
                'excerpt' => 'If you send padala, move that fund slice first — before everyday spending creeps in.',
                'body' => "Many Filipino planners reserve a **Family Support** fund.\n\nTransfer it on payday, not at month end when the account is already thin.",
                'content_type' => ContentPostType::Reminder,
                'publish_scope' => ContentPublishScope::External,
                'status' => ContentPostStatus::Published,
                'author_id' => $author->id,
                'published_at' => now(),
                'reading_time_minutes' => 1,
            ],
        );
    }
}
