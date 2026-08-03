<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'emailTeamInvitations' => ['required', 'boolean'],
            'emailPendingActions' => ['required', 'boolean'],
            'emailBillingReminders' => ['required', 'boolean'],
            'inAppTeamInvitations' => ['required', 'boolean'],
            'inAppPendingActions' => ['required', 'boolean'],
            'inAppBillingReminders' => ['required', 'boolean'],
            'pushEnabled' => ['required', 'boolean'],
            'pushPendingActions' => ['required', 'boolean'],
            'pushBillingReminders' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function preferenceAttributes(): array
    {
        return [
            'email_team_invitations' => $this->boolean('emailTeamInvitations'),
            'email_pending_actions' => $this->boolean('emailPendingActions'),
            'email_billing_reminders' => $this->boolean('emailBillingReminders'),
            'in_app_team_invitations' => $this->boolean('inAppTeamInvitations'),
            'in_app_pending_actions' => $this->boolean('inAppPendingActions'),
            'in_app_billing_reminders' => $this->boolean('inAppBillingReminders'),
            'push_enabled' => $this->boolean('pushEnabled'),
            'push_pending_actions' => $this->boolean('pushPendingActions'),
            'push_billing_reminders' => $this->boolean('pushBillingReminders'),
        ];
    }
}
