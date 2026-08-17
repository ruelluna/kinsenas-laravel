<?php

namespace Database\Seeders;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPostType;
use App\Enums\ContentPublishScope;
use App\Enums\ContentSeriesStatus;
use App\Enums\PlatformRole;
use App\Models\ContentPost;
use App\Models\ContentPostCategory;
use App\Models\ContentSeries;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::role(PlatformRole::PlatformAdmin->value)->first()
            ?? User::role(PlatformRole::Author->value)->first()
            ?? User::factory()->author()->create(['email' => 'content-admin@kinsenas.test']);

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
                'body' => '<h2>Why lock?</h2><p>When you record income, Kinsenas splits it across your funds. <strong>Locking</strong> confirms you are ready to act on that split.</p><ul><li>Plan stays stable for the pay period</li><li>Reports reflect what you committed to</li><li>Transfers align with your formula</li></ul>',
            ],
            [
                'episode_number' => 2,
                'title' => 'Move money to your banks',
                'slug' => 'move-money-to-banks',
                'excerpt' => 'After locking, transfer each fund slice to the bank you assigned — Kinsenas tracks what you planned.',
                'body' => '<h2>Transfers match your plan</h2><p>Use the <strong>Transfers</strong> screen to move from each fund to its bank account.</p><ol><li>Open Transfers</li><li>Pick source fund and destination bank</li><li>Confirm when done in your banking app</li></ol>',
            ],
            [
                'episode_number' => 3,
                'title' => 'Review fund health',
                'slug' => 'review-fund-health',
                'excerpt' => 'A quick payday ritual: check low balances and log your first spend.',
                'body' => '<h2>Stay on track</h2><p>Before the next sweldo, glance at <strong>Dashboard</strong> and <strong>Reports</strong>.</p><ul><li>Funds above 90% used need attention</li><li>Log spending as you go — do not wait until month end</li></ul>',
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
                'body' => '<p>Pick the <strong>smallest real expense</strong> after payday and log it under Spending.</p><p>That single entry builds the habit of matching your plan to real life.</p>',
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
                'body' => '<p>Many Filipino planners reserve a <strong>Family Support</strong> fund.</p><p>Transfer it on payday, not at month end when the account is already thin.</p>',
                'content_type' => ContentPostType::Reminder,
                'publish_scope' => ContentPublishScope::External,
                'status' => ContentPostStatus::Published,
                'author_id' => $author->id,
                'published_at' => now(),
                'reading_time_minutes' => 1,
            ],
        );

        $paydayIncome = ContentPostCategory::query()->where('slug', 'payday-income')->first();
        $usingKinsenas = ContentPostCategory::query()->where('slug', 'using-kinsenas')->first();
        $spendingBudget = ContentPostCategory::query()->where('slug', 'spending-budget')->first();
        $familyFinances = ContentPostCategory::query()->where('slug', 'family-finances')->first();
        $savingFunds = ContentPostCategory::query()->where('slug', 'saving-funds')->first();

        if ($paydayIncome && $usingKinsenas) {
            foreach (['why-lock-your-income', 'move-money-to-banks', 'review-fund-health'] as $slug) {
                ContentPost::query()->where('slug', $slug)->first()?->categories()->sync([
                    $paydayIncome->id,
                    $usingKinsenas->id,
                ]);
            }
        }

        if ($spendingBudget && $usingKinsenas) {
            ContentPost::query()->where('slug', 'payday-reminder-log-first-spend')->first()?->categories()->sync([
                $spendingBudget->id,
                $usingKinsenas->id,
            ]);
        }

        if ($familyFinances && $savingFunds) {
            ContentPost::query()->where('slug', 'payday-reminder-family-support')->first()?->categories()->sync([
                $familyFinances->id,
                $savingFunds->id,
            ]);
        }
    }
}
