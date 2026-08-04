<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_distribution_todos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('income_period_id')->constrained('income_periods')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->foreignUuid('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->text('amount_encrypted');
            $table->string('status');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['income_period_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_distribution_todos');
    }
};
