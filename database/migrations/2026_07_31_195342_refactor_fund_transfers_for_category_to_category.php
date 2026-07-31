<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('fund_transfers')->delete();

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['bank_id']);
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'bank_id']);
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->foreignUuid('from_category_id')
                ->after('savings_plan_id')
                ->constrained('savings_categories')
                ->cascadeOnDelete();
            $table->foreignUuid('to_category_id')
                ->after('from_category_id')
                ->constrained('savings_categories')
                ->cascadeOnDelete();
            $table->foreignUuid('from_bank_id')
                ->nullable()
                ->after('to_category_id')
                ->constrained('banks')
                ->nullOnDelete();
            $table->foreignUuid('to_bank_id')
                ->nullable()
                ->after('from_bank_id')
                ->constrained('banks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('fund_transfers')->delete();

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->dropForeign(['from_category_id']);
            $table->dropForeign(['to_category_id']);
            $table->dropForeign(['from_bank_id']);
            $table->dropForeign(['to_bank_id']);
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->dropColumn(['from_category_id', 'to_category_id', 'from_bank_id', 'to_bank_id']);
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->foreignUuid('category_id')
                ->after('savings_plan_id')
                ->constrained('savings_categories')
                ->cascadeOnDelete();
            $table->foreignUuid('bank_id')
                ->after('category_id')
                ->constrained('banks')
                ->cascadeOnDelete();
        });
    }
};
