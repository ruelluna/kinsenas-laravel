<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fund_added_entries') || Schema::hasTable('fund_opening_balance_entries')) {
            return;
        }

        Schema::create('fund_added_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('savings_plan_id')->constrained('savings_plans')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('savings_categories')->nullOnDelete();
            $table->string('category_name');
            $table->text('amount_encrypted');
            $table->date('added_on');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_added_entries');
    }
};
