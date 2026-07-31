<?php

namespace Database\Seeders;

use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsFormulaTemplateCategory;
use Illuminate\Database\Seeder;

class SavingsFormulaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $abundant = SavingsFormulaTemplate::query()->updateOrCreate(
            ['slug' => 'abundant-formula'],
            [
                'name' => 'The Abundant Formula Savings',
                'description' => 'Everyday Fund 70%, Savings 20%, Tithe 10%',
                'best_for' => 'Best for members who want a simple three-bucket split with most income for daily needs.',
                'video_embed_url' => null,
                'is_system' => true,
            ],
        );

        if ($abundant->categories()->count() === 0) {
            $this->seedCategories($abundant->id, [
                ['Everyday Fund', 70, 'Day-to-day expenses — bills, food, transport, and household needs.'],
                ['Savings', 20, 'Long-term savings and goals you want to grow over time.'],
                ['Tithe', 10, 'Giving or faith-based allocation — adjust the name if you use a different term.'],
            ]);
        }

        $trc = SavingsFormulaTemplate::query()->updateOrCreate(
            ['slug' => 'trc-savings'],
            [
                'name' => 'TRC Savings',
                'description' => 'Tithe, Educational, Enjoyment, Empower, Emergency, Emancipation, Everyday',
                'best_for' => 'Best for members who want a detailed seven-fund plan with dedicated buckets for education, emergencies, and future freedom.',
                'video_embed_url' => null,
                'is_system' => true,
            ],
        );

        if ($trc->categories()->count() === 0) {
            $this->seedCategories($trc->id, [
                ['Tithe', 10, 'Giving or faith-based allocation.'],
                ['Educational', 5, 'Courses, books, and skills that help you grow.'],
                ['Enjoyment', 5, 'Fun and recreation without guilt.'],
                ['Empower Fund', 5, 'Investing in tools or opportunities that increase your earning power.'],
                ['Emergency Fund', 5, 'Unexpected expenses and financial shocks.'],
                ['Emancipation Fund', 20, 'Building toward financial independence and larger future goals.'],
                ['Everyday Fund', 50, 'Regular living expenses for the month.'],
            ]);
        }
    }

    /**
     * @param  list<array{0: string, 1: int|float, 2?: string|null}>  $rows
     */
    private function seedCategories(string $templateId, array $rows): void
    {
        foreach ($rows as $index => $row) {
            [$name, $percentage] = $row;
            $description = $row[2] ?? null;

            SavingsFormulaTemplateCategory::query()->create([
                'template_id' => $templateId,
                'name' => $name,
                'percentage' => $percentage,
                'description' => $description,
                'sort_order' => $index,
            ]);
        }
    }
}
