<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fund_opening_balance_entries')) {
            return;
        }

        if (! Schema::hasTable('fund_added_entries')) {
            Schema::rename('fund_opening_balance_entries', 'fund_added_entries');

            return;
        }

        DB::table('fund_added_entries')->insertOrIgnore(
            DB::table('fund_opening_balance_entries')->get()->map(fn ($row) => (array) $row)->all(),
        );

        Schema::drop('fund_opening_balance_entries');
    }

    public function down(): void
    {
        if (Schema::hasTable('fund_opening_balance_entries') || ! Schema::hasTable('fund_added_entries')) {
            return;
        }

        Schema::rename('fund_added_entries', 'fund_opening_balance_entries');
    }
};
