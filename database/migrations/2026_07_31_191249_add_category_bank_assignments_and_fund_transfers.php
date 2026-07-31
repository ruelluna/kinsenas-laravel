<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->foreignUuid('bank_institution_id')
                ->nullable()
                ->after('team_id')
                ->constrained('bank_institutions')
                ->nullOnDelete();
        });

        Schema::create('bank_savings_category', function (Blueprint $table) {
            $table->foreignUuid('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->foreignUuid('savings_category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->primary(['bank_id', 'savings_category_id']);
        });

        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('savings_plan_id')->constrained('savings_plans')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->foreignUuid('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->text('amount_encrypted');
            $table->string('description');
            $table->date('transferred_on');
            $table->string('status')->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
        Schema::dropIfExists('bank_savings_category');

        Schema::table('banks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_institution_id');
        });
    }
};
