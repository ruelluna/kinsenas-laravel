<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('savings_formula_templates', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_system');
        });

        DB::table('savings_formula_templates')
            ->where('slug', 'abundant-formula')
            ->update(['sort_order' => 1, 'updated_at' => now()]);

        DB::table('savings_formula_templates')
            ->where('slug', 'trc-savings')
            ->update(['sort_order' => 2, 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_formula_templates', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
