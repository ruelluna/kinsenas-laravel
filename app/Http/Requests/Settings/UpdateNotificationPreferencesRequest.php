<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'pushTeamInvitations' => ['required', 'boolean'],
            'pushPendingActions' => ['required', 'boolean'],
            'pushLowFundBalance' => ['required', 'boolean'],
            'pushBillingReminders' => ['required', 'boolean'],
            'pushTeamActivity' => ['required', 'boolean'],
            'pushIncomeReminders' => ['required', 'boolean'],
            'pushActionUpdates' => ['required', 'boolean'],
            'paydayDayOfMonth' => ['nullable', 'integer', Rule::in(range(1, 28))],
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
            'push_team_invitations' => $this->boolean('pushTeamInvitations'),
            'push_pending_actions' => $this->boolean('pushPendingActions'),
            'push_low_fund_balance' => $this->boolean('pushLowFundBalance'),
            'push_billing_reminders' => $this->boolean('pushBillingReminders'),
            'push_team_activity' => $this->boolean('pushTeamActivity'),
            'push_income_reminders' => $this->boolean('pushIncomeReminders'),
            'push_action_updates' => $this->boolean('pushActionUpdates'),
        ];
    }
}
