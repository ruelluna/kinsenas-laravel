<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_categories', function (Blueprint $table) {
            $table->foreignUuid('bank_id')
                ->nullable()
                ->after('sort_order')
                ->constrained('banks')
                ->nullOnDelete();
        });

        if (Schema::hasTable('bank_savings_category')) {
            $assignments = DB::table('bank_savings_category')
                ->select('savings_category_id', 'bank_id')
                ->orderBy('savings_category_id')
                ->get()
                ->groupBy('savings_category_id');

            foreach ($assignments as $categoryId => $rows) {
                DB::table('savings_categories')
                    ->where('id', $categoryId)
                    ->update(['bank_id' => $rows->first()->bank_id]);
            }

            Schema::drop('bank_savings_category');
        }
    }

    public function down(): void
    {
        Schema::create('bank_savings_category', function (Blueprint $table) {
            $table->foreignUuid('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->foreignUuid('savings_category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->primary(['bank_id', 'savings_category_id']);
        });

        $categories = DB::table('savings_categories')
            ->whereNotNull('bank_id')
            ->get(['id', 'bank_id']);

        foreach ($categories as $category) {
            DB::table('bank_savings_category')->insert([
                'bank_id' => $category->bank_id,
                'savings_category_id' => $category->id,
            ]);
        }

        Schema::table('savings_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_id');
        });
    }
};
