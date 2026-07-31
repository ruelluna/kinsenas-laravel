<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_spends', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('savings_plan_id')->constrained('savings_plans')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('savings_categories')->cascadeOnDelete();
            $table->text('amount_encrypted');
            $table->string('description');
            $table->date('spent_on');
            $table->foreignUuid('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->foreignUuid('recipient_id')->nullable()->constrained('recipients')->nullOnDelete();
            $table->string('status')->default('confirmed');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (Schema::hasTable('transfers')) {
            $transfers = DB::table('transfers')
                ->join('income_periods', 'transfers.income_period_id', '=', 'income_periods.id')
                ->select([
                    'transfers.id',
                    'income_periods.plan_id as savings_plan_id',
                    'transfers.category_id',
                    'transfers.amount_encrypted',
                    'transfers.notes',
                    'transfers.transferred_on',
                    'transfers.bank_id',
                    'transfers.recipient_id',
                    'transfers.status',
                    'transfers.confirmed_at',
                    'transfers.confirmed_by_user_id',
                    'transfers.created_at',
                    'transfers.updated_at',
                ])
                ->get();

            foreach ($transfers as $transfer) {
                DB::table('fund_spends')->insert([
                    'id' => $transfer->id ?? (string) Str::uuid7(),
                    'savings_plan_id' => $transfer->savings_plan_id,
                    'category_id' => $transfer->category_id,
                    'amount_encrypted' => $transfer->amount_encrypted,
                    'description' => $transfer->notes ?: 'Transfer',
                    'spent_on' => $transfer->transferred_on,
                    'bank_id' => $transfer->bank_id,
                    'recipient_id' => $transfer->recipient_id,
                    'status' => $transfer->status,
                    'confirmed_at' => $transfer->confirmed_at,
                    'confirmed_by_user_id' => $transfer->confirmed_by_user_id,
                    'created_at' => $transfer->created_at,
                    'updated_at' => $transfer->updated_at,
                ]);
            }

            Schema::drop('transfers');
        }
    }

    public function down(): void
    {
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

        Schema::dropIfExists('fund_spends');
    }
};
