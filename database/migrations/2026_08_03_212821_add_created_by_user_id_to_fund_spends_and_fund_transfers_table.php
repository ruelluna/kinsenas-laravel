<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_spends', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('confirmed_by_user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('confirmed_by_user_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fund_spends', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
        });
    }
};
