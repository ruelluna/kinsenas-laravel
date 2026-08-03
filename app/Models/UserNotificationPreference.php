<?php

namespace App\Models;

use Database\Factories\UserNotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    /** @use HasFactory<UserNotificationPreferenceFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'email_team_invitations',
        'email_pending_actions',
        'email_billing_reminders',
        'in_app_team_invitations',
        'in_app_pending_actions',
        'in_app_billing_reminders',
        'push_enabled',
        'push_pending_actions',
        'push_billing_reminders',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_team_invitations' => 'boolean',
            'email_pending_actions' => 'boolean',
            'email_billing_reminders' => 'boolean',
            'in_app_team_invitations' => 'boolean',
            'in_app_pending_actions' => 'boolean',
            'in_app_billing_reminders' => 'boolean',
            'push_enabled' => 'boolean',
            'push_pending_actions' => 'boolean',
            'push_billing_reminders' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, bool>
     */
    public static function defaultAttributes(): array
    {
        return [
            'email_team_invitations' => true,
            'email_pending_actions' => true,
            'email_billing_reminders' => true,
            'in_app_team_invitations' => true,
            'in_app_pending_actions' => true,
            'in_app_billing_reminders' => true,
            'push_enabled' => false,
            'push_pending_actions' => true,
            'push_billing_reminders' => true,
        ];
    }
}
