<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('beta_enrolled_at')
            ->where('beta_launch_discount_eligible', false)
            ->update(['beta_launch_discount_eligible' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('beta_access_code_id');
            $table->dropConstrainedForeignId('beta_approved_by');
            $table->dropColumn(['beta_application_status', 'beta_approved_at']);
        });

        Schema::dropIfExists('beta_access_codes');
        Schema::dropIfExists('beta_access_code_batches');
    }

    public function down(): void
    {
        Schema::create('beta_access_code_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('beta_access_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->nullable()->constrained('beta_access_code_batches')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('label');
            $table->string('type');
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('redemptions_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('beta_application_status')->nullable()->after('beta_enrolled_at');
            $table->timestamp('beta_approved_at')->nullable()->after('beta_application_status');
            $table->foreignId('beta_approved_by')->nullable()->after('beta_approved_at')->constrained('users')->nullOnDelete();
            $table->foreignUuid('beta_access_code_id')->nullable()->after('beta_approved_by')->constrained('beta_access_codes')->nullOnDelete();
        });
    }
};
