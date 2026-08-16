<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_spend_reimbursements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fund_spend_id')->constrained('fund_spends')->cascadeOnDelete();
            $table->foreignUuid('savings_plan_id')->constrained('savings_plans')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->text('amount_encrypted');
            $table->date('received_on');
            $table->foreignUuid('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_spend_reimbursements');
    }
};
