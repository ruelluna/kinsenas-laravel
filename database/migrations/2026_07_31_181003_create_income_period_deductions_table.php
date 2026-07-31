<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_period_deductions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('income_period_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->text('amount_encrypted');
            $table->timestamps();

            $table->unique(['income_period_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_period_deductions');
    }
};
