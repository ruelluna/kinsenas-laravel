<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_CATEGORY_NAME = 'Emancipation Fund';

    private const NEW_CATEGORY_NAME = 'Savings';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $templateId = DB::table('savings_formula_templates')
            ->where('slug', 'trc-savings')
            ->value('id');

        if ($templateId !== null) {
            DB::table('savings_formula_template_categories')
                ->where('template_id', $templateId)
                ->where('name', self::LEGACY_CATEGORY_NAME)
                ->update([
                    'name' => self::NEW_CATEGORY_NAME,
                    'updated_at' => now(),
                ]);

            $template = DB::table('savings_formula_templates')
                ->where('id', $templateId)
                ->first(['best_for']);

            if ($template !== null && is_string($template->best_for) && $template->best_for !== '') {
                DB::table('savings_formula_templates')
                    ->where('id', $templateId)
                    ->update([
                        'best_for' => str_replace('(Emancipation)', '(Savings)', $template->best_for),
                        'updated_at' => now(),
                    ]);
            }
        }

        DB::table('savings_categories')
            ->where('name', self::LEGACY_CATEGORY_NAME)
            ->update([
                'name' => self::NEW_CATEGORY_NAME,
                'updated_at' => now(),
            ]);

        DB::table('fund_added_entries')
            ->where('category_name', self::LEGACY_CATEGORY_NAME)
            ->update([
                'category_name' => self::NEW_CATEGORY_NAME,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $templateId = DB::table('savings_formula_templates')
            ->where('slug', 'trc-savings')
            ->value('id');

        if ($templateId !== null) {
            DB::table('savings_formula_template_categories')
                ->where('template_id', $templateId)
                ->where('name', self::NEW_CATEGORY_NAME)
                ->where('percentage', 20)
                ->update([
                    'name' => self::LEGACY_CATEGORY_NAME,
                    'updated_at' => now(),
                ]);

            $template = DB::table('savings_formula_templates')
                ->where('id', $templateId)
                ->first(['best_for']);

            if ($template !== null && is_string($template->best_for) && $template->best_for !== '') {
                DB::table('savings_formula_templates')
                    ->where('id', $templateId)
                    ->update([
                        'best_for' => str_replace('(Savings)', '(Emancipation)', $template->best_for),
                        'updated_at' => now(),
                    ]);
            }
        }

        $sevenBucketPlanIds = DB::table('savings_categories')
            ->select('plan_id')
            ->groupBy('plan_id')
            ->havingRaw('count(*) = 7')
            ->pluck('plan_id');

        $revertedCategoryIds = DB::table('savings_categories')
            ->where('name', self::NEW_CATEGORY_NAME)
            ->where('percentage', 20)
            ->whereIn('plan_id', $sevenBucketPlanIds)
            ->pluck('id');

        DB::table('savings_categories')
            ->whereIn('id', $revertedCategoryIds)
            ->update([
                'name' => self::LEGACY_CATEGORY_NAME,
                'updated_at' => now(),
            ]);

        DB::table('fund_added_entries')
            ->where('category_name', self::NEW_CATEGORY_NAME)
            ->whereIn('category_id', $revertedCategoryIds)
            ->update([
                'category_name' => self::LEGACY_CATEGORY_NAME,
                'updated_at' => now(),
            ]);
    }
};
