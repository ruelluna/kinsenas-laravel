<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_notification_preferences', function (Blueprint $table) {
            $table->boolean('push_team_invitations')->default(true)->after('push_enabled');
            $table->boolean('push_low_fund_balance')->default(true)->after('push_pending_actions');
            $table->boolean('push_team_activity')->default(true)->after('push_billing_reminders');
            $table->boolean('push_income_reminders')->default(true)->after('push_team_activity');
            $table->boolean('push_action_updates')->default(false)->after('push_income_reminders');
        });
    }

    public function down(): void
    {
        Schema::table('user_notification_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'push_team_invitations',
                'push_low_fund_balance',
                'push_team_activity',
                'push_income_reminders',
                'push_action_updates',
            ]);
        });
    }
};
