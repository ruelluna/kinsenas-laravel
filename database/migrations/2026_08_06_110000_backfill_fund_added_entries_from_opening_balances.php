<?php

use App\Models\FundAddedEntry;
use App\Models\SavingsCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        SavingsCategory::query()
            ->whereNotNull('opening_balance_encrypted')
            ->with('plan')
            ->each(function (SavingsCategory $category): void {
                $openingBalance = $category->opening_balance_encrypted;

                if ($openingBalance === null || bccomp($openingBalance, '0', 2) !== 1) {
                    return;
                }

                $recordedTotal = FundAddedEntry::query()
                    ->where('category_id', $category->id)
                    ->get()
                    ->reduce(
                        fn (string $carry, FundAddedEntry $entry): string => bcadd(
                            $carry,
                            $entry->amount_encrypted ?? '0.00',
                            2,
                        ),
                        '0.00',
                    );

                $gap = bcsub($openingBalance, $recordedTotal, 2);

                if (bccomp($gap, '0', 2) !== 1) {
                    return;
                }

                FundAddedEntry::query()->create([
                    'id' => (string) Str::uuid7(),
                    'savings_plan_id' => $category->plan_id,
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'amount_encrypted' => $gap,
                    'added_on' => $category->updated_at?->toDateString() ?? now()->toDateString(),
                    'created_by_user_id' => null,
                ]);
            });
    }

    public function down(): void
    {
        // Data backfill — no rollback.
    }
};
