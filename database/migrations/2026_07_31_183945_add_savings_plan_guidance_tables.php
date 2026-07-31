<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_plan_page_guidance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('chooser_intro')->nullable();
            $table->string('chooser_video_url')->nullable();
            $table->text('before_choose_note')->nullable();
            $table->text('after_income_rules')->nullable();
            $table->string('after_income_video_url')->nullable();
            $table->timestamps();
        });

        Schema::table('savings_formula_templates', function (Blueprint $table) {
            $table->text('best_for')->nullable()->after('description');
            $table->string('video_embed_url')->nullable()->after('best_for');
        });

        Schema::table('savings_formula_template_categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('percentage');
        });
    }

    public function down(): void
    {
        Schema::table('savings_formula_template_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('savings_formula_templates', function (Blueprint $table) {
            $table->dropColumn(['best_for', 'video_embed_url']);
        });

        Schema::dropIfExists('savings_plan_page_guidance');
    }
};
