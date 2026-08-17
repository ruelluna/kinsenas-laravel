<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_CATEGORY_NAME = 'Empower Fund';

    private const NEW_CATEGORY_NAME = 'Utility';

    private const NEW_CATEGORY_DESCRIPTION = 'Repairs, maintenance, and household utilities.';

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
                    'description' => self::NEW_CATEGORY_DESCRIPTION,
                    'updated_at' => now(),
                ]);
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
                ->where('percentage', 5)
                ->update([
                    'name' => self::LEGACY_CATEGORY_NAME,
                    'description' => 'Investing in tools or opportunities that increase your earning power.',
                    'updated_at' => now(),
                ]);
        }

        $sevenBucketPlanIds = DB::table('savings_categories')
            ->select('plan_id')
            ->groupBy('plan_id')
            ->havingRaw('count(*) = 7')
            ->pluck('plan_id');

        $revertedCategoryIds = DB::table('savings_categories')
            ->where('name', self::NEW_CATEGORY_NAME)
            ->where('percentage', 5)
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
