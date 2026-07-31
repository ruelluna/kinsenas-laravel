<?php

namespace Database\Seeders;

use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsFormulaTemplateCategory;
use Illuminate\Database\Seeder;

class SavingsFormulaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $abundant = SavingsFormulaTemplate::query()->firstOrCreate(
            ['slug' => 'abundant-formula'],
            [
                'name' => 'The Abundant Formula Savings',
                'description' => 'Everyday Fund 70%, Savings 20%, Tithe 10%',
                'is_system' => true,
            ],
        );

        if ($abundant->categories()->count() === 0) {
            $this->seedCategories($abundant->id, [
                ['Everyday Fund', 70],
                ['Savings', 20],
                ['Tithe', 10],
            ]);
        }

        $trc = SavingsFormulaTemplate::query()->firstOrCreate(
            ['slug' => 'trc-savings'],
            [
                'name' => 'TRC Savings',
                'description' => 'Tithe, Educational, Enjoyment, Empower, Emergency, Emancipation, Everyday',
                'is_system' => true,
            ],
        );

        if ($trc->categories()->count() === 0) {
            $this->seedCategories($trc->id, [
                ['Tithe', 10],
                ['Educational', 5],
                ['Enjoyment', 5],
                ['Empower Fund', 5],
                ['Emergency Fund', 5],
                ['Emancipation Fund', 20],
                ['Everyday Fund', 50],
            ]);
        }
    }

    /**
     * @param  list<array{0: string, 1: int|float}>  $rows
     */
    private function seedCategories(string $templateId, array $rows): void
    {
        foreach ($rows as $index => [$name, $percentage]) {
            SavingsFormulaTemplateCategory::query()->create([
                'template_id' => $templateId,
                'name' => $name,
                'percentage' => $percentage,
                'sort_order' => $index,
            ]);
        }
    }
}
