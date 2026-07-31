<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_vaults', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('wrapped_dek');
            $table->text('recovery_wrapped_dek')->nullable();
            $table->string('salt', 64);
            $table->string('recovery_key_hash', 128)->nullable();
            $table->unsignedSmallInteger('dek_version')->default(1);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::create('team_vaults', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('wrapped_dek');
            $table->string('salt', 64);
            $table->unsignedSmallInteger('dek_version')->default(1);
            $table->timestamps();
        });

        Schema::create('savings_formula_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        Schema::create('savings_formula_template_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained('savings_formula_templates')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('percentage', 5, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('savings_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('currency', 3)->default('PHP');
            $table->boolean('is_shared_with_team')->default(false);
            $table->timestamps();

            $table->unique(['team_id', 'created_by_user_id']);
        });

        Schema::create('savings_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('savings_plans')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('percentage', 5, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('income_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('savings_plans')->cascadeOnDelete();
            $table->text('amount_encrypted');
            $table->date('period_start');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('income_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('income_period_id')->constrained('income_periods')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->text('amount_encrypted');
            $table->timestamps();

            $table->unique(['income_period_id', 'category_id']);
        });

        Schema::create('banks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('account_label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('income_period_id')->constrained('income_periods')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->foreignUuid('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->foreignUuid('recipient_id')->constrained('recipients')->cascadeOnDelete();
            $table->text('amount_encrypted');
            $table->string('status')->default('pending');
            $table->date('transferred_on');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('trial_days')->default(14);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscription_plan_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('interval');
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('PHP');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['plan_id', 'interval']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_platform_admin')->default(false)->after('current_team_id');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('subscription_plans');
            $table->string('status')->default('trialing');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_method_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider');
            $table->string('label');
            $table->string('qr_image_path')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('plan_price_id')->constrained('subscription_plan_prices');
            $table->string('reference_number');
            $table->string('proof_image_path')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
        Schema::dropIfExists('payment_method_configs');
        Schema::dropIfExists('subscriptions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_platform_admin');
        });
        Schema::dropIfExists('subscription_plan_prices');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('recipients');
        Schema::dropIfExists('banks');
        Schema::dropIfExists('income_allocations');
        Schema::dropIfExists('income_periods');
        Schema::dropIfExists('savings_categories');
        Schema::dropIfExists('savings_plans');
        Schema::dropIfExists('savings_formula_template_categories');
        Schema::dropIfExists('savings_formula_templates');
        Schema::dropIfExists('team_vaults');
        Schema::dropIfExists('user_vaults');
    }
};
