<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_categories', function (Blueprint $table) {
            $table->string('allocation_type')->default('percentage')->after('name');
            $table->decimal('percentage', 5, 2)->nullable()->change();
            $table->string('deduction_mode')->nullable()->after('percentage');
            $table->decimal('deduction_value', 12, 2)->nullable()->after('deduction_mode');
            $table->foreignUuid('deduct_from_category_id')
                ->nullable()
                ->after('deduction_value')
                ->constrained('savings_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('savings_categories', function (Blueprint $table) {
            $table->dropForeign(['deduct_from_category_id']);
            $table->dropColumn([
                'allocation_type',
                'deduction_mode',
                'deduction_value',
                'deduct_from_category_id',
            ]);
        });
    }
};
