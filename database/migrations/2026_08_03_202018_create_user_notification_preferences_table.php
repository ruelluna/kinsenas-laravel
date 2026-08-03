<?php

use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('email_team_invitations')->default(true);
            $table->boolean('email_pending_actions')->default(true);
            $table->boolean('email_billing_reminders')->default(true);
            $table->boolean('in_app_team_invitations')->default(true);
            $table->boolean('in_app_pending_actions')->default(true);
            $table->boolean('in_app_billing_reminders')->default(true);
            $table->boolean('push_enabled')->default(false);
            $table->boolean('push_pending_actions')->default(true);
            $table->boolean('push_billing_reminders')->default(true);
            $table->timestamps();
        });

        User::query()->each(function (User $user): void {
            $user->notificationPreferences()->create(UserNotificationPreference::defaultAttributes());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
