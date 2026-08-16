<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_TEMPLATE_NAME = 'TRC — Truly Rich Club';

    private const NEW_TEMPLATE_NAME = '7 Buckets';

    private const NEW_TEMPLATE_DESCRIPTION = 'A seven-bucket payday split that assigns every peso a job before you spend.';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('savings_formula_templates')
            ->where('slug', 'trc-savings')
            ->update([
                'name' => self::NEW_TEMPLATE_NAME,
                'description' => self::NEW_TEMPLATE_DESCRIPTION,
                'updated_at' => now(),
            ]);

        DB::table('savings_plans')
            ->where('name', self::LEGACY_TEMPLATE_NAME)
            ->update([
                'name' => self::NEW_TEMPLATE_NAME,
                'updated_at' => now(),
            ]);

        $guidance = DB::table('savings_plan_page_guidance')->first();

        if ($guidance === null) {
            return;
        }

        $updates = [];

        foreach (['chooser_intro', 'before_choose_note', 'after_income_rules'] as $column) {
            $value = $guidance->{$column} ?? null;

            if (! is_string($value) || $value === '') {
                continue;
            }

            $updates[$column] = str_replace(
                ['TRC (Truly Rich Club)', 'TRC — Truly Rich Club', 'TRC'],
                ['7 Buckets', '7 Buckets', '7 Buckets'],
                $value,
            );
        }

        if ($updates !== []) {
            $updates['updated_at'] = now();

            DB::table('savings_plan_page_guidance')
                ->where('id', $guidance->id)
                ->update($updates);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('savings_formula_templates')
            ->where('slug', 'trc-savings')
            ->update([
                'name' => self::LEGACY_TEMPLATE_NAME,
                'description' => 'TRC stands for Truly Rich Club — a seven-bucket payday split that assigns every peso a job before you spend.',
                'updated_at' => now(),
            ]);

        DB::table('savings_plans')
            ->where('name', self::NEW_TEMPLATE_NAME)
            ->update([
                'name' => self::LEGACY_TEMPLATE_NAME,
                'updated_at' => now(),
            ]);

        $guidance = DB::table('savings_plan_page_guidance')->first();

        if ($guidance === null) {
            return;
        }

        $updates = [];

        foreach (['chooser_intro', 'before_choose_note', 'after_income_rules'] as $column) {
            $value = $guidance->{$column} ?? null;

            if (! is_string($value) || $value === '') {
                continue;
            }

            $updates[$column] = str_replace('7 Buckets', 'TRC (Truly Rich Club)', $value);
        }

        if ($updates !== []) {
            $updates['updated_at'] = now();

            DB::table('savings_plan_page_guidance')
                ->where('id', $guidance->id)
                ->update($updates);
        }
    }
};
