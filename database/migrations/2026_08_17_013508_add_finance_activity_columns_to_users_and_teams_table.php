<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('finance_activity_score')->default(0)->after('payday_day_of_month');
            $table->string('finance_activity_tier', 32)->default('inactive')->after('finance_activity_score');
            $table->timestamp('last_finance_activity_at')->nullable()->after('finance_activity_tier');

            $table->index('finance_activity_tier');
            $table->index('finance_activity_score');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedTinyInteger('finance_activity_score')->default(0)->after('is_personal');
            $table->string('finance_activity_tier', 32)->default('inactive')->after('finance_activity_score');
            $table->timestamp('last_finance_activity_at')->nullable()->after('finance_activity_tier');

            $table->index('finance_activity_tier');
            $table->index('finance_activity_score');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['finance_activity_tier']);
            $table->dropIndex(['finance_activity_score']);
            $table->dropColumn([
                'finance_activity_score',
                'finance_activity_tier',
                'last_finance_activity_at',
            ]);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex(['finance_activity_tier']);
            $table->dropIndex(['finance_activity_score']);
            $table->dropColumn([
                'finance_activity_score',
                'finance_activity_tier',
                'last_finance_activity_at',
            ]);
        });
    }
};
