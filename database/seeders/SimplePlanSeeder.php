<?php

namespace Database\Seeders;

use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsFormulaTemplateCategory;
use Illuminate\Database\Seeder;

class SimplePlanSeeder extends Seeder
{
    public function run(): void
    {
        $simple = SavingsFormulaTemplate::query()->updateOrCreate(
            ['slug' => 'simple-plan'],
            [
                'name' => 'The Simple Plan',
                'description' => 'No, not the pop-punk band.',
                'best_for' => 'Best for members who want the simplest split — most income for daily life, the rest for savings.',
                'video_embed_url' => null,
                'is_system' => true,
                'sort_order' => 0,
            ],
        );

        if ($simple->categories()->count() === 0) {
            $this->seedCategories($simple->id, [
                ['Everyday Fund', 80, 'Day-to-day expenses — bills, food, transport, and household needs.'],
                ['Savings', 20, 'Long-term savings and goals you want to grow over time.'],
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
