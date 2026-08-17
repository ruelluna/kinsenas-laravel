<?php

namespace Database\Seeders;

use App\Enums\ContentPostStatus;
use App\Models\ContentPostCategory;
use Illuminate\Database\Seeder;

class ContentPostCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'payday-income',
                'name' => 'Payday & income',
                'description' => 'Locking sweldo, income rituals, and payday planning.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'saving-funds',
                'name' => 'Saving & funds',
                'description' => 'Emergency funds, sinking funds, and formula-driven saving.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'spending-budget',
                'name' => 'Spending & budgeting',
                'description' => 'Tracking spends, everyday budgets, and staying on plan.',
                'sort_order' => 3,
            ],
            [
                'slug' => 'family-finances',
                'name' => 'Family finances',
                'description' => 'Padala, household support, and shared money decisions.',
                'sort_order' => 4,
            ],
            [
                'slug' => 'side-income',
                'name' => 'Side income',
                'description' => 'Editorial guidance on earning beyond your main job.',
                'sort_order' => 5,
            ],
            [
                'slug' => 'debt-credit',
                'name' => 'Debt & credit',
                'description' => 'Loans, credit cards, and paying down obligations.',
                'sort_order' => 6,
            ],
            [
                'slug' => 'mindset-habits',
                'name' => 'Mindset & habits',
                'description' => 'Behavior, consistency, and building better money habits.',
                'sort_order' => 7,
            ],
            [
                'slug' => 'using-kinsenas',
                'name' => 'Using Kinsenas',
                'description' => 'How-to guides for plans, transfers, reports, and app features.',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            ContentPostCategory::query()->firstOrCreate(
                ['slug' => $category['slug']],
                [
                    ...$category,
                    'status' => ContentPostStatus::Published,
                ],
            );
        }
    }
}
