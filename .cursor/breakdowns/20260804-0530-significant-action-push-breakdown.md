# Significant-action push notifications breakdown

**Date:** 2026-08-04

## Summary

Extended the notification system with granular per-event Web Push toggles, Web Push coverage for significant user actions (team invites, low balance, past due, team activity, income reminders, action confirmations, beta approval), payday-based income reminders, and platform-admin test push tooling (Artisan command + admin UI).

## Changelog

- **Settings → Notifications** — per-event push toggles (team invitations, pending actions, low fund balance, billing, team activity, income reminders, action updates) plus optional payday day-of-month for income reminders
- Team invitations and low fund balance now send Web Push when the matching toggle is on and the device is subscribed
- Subscription past due, invitation accepted, pending action confirmed, income reminder, and beta approved events write to the inbox and can push per preferences
- Platform admins can send test push via **Admin → Push test** or `notifications:send-test-push`
- Daily scheduled command `notifications:income-reminder` for payday reminders

## Files touched

### Database
- `database/migrations/2026_08_03_212706_add_granular_push_preferences_to_user_notification_preferences_table.php`
- `database/migrations/2026_08_03_212707_add_payday_day_of_month_to_users_table.php`
- `database/migrations/2026_08_03_212821_add_created_by_user_id_to_fund_spends_and_fund_transfers_table.php`

### Backend
- `app/Enums/NotificationKind.php`
- `app/Models/UserNotificationPreference.php`, `User.php`, `FundSpend.php`, `FundTransfer.php`
- `app/Services/Notifications/NotificationPreferenceService.php`
- `app/Services/Notifications/SubscriptionNotificationService.php`
- `app/Services/Notifications/PendingActionConfirmedNotificationService.php`
- `app/Notifications/Billing/SubscriptionPastDue.php`, `BetaApproved.php`
- `app/Notifications/Teams/TeamInvitationAccepted.php` + `TeamInvitation.php` (`toWebPush`)
- `app/Notifications/Savings/LowFundBalance.php`, `PendingActionConfirmed.php`, `IncomeReminder.php`
- `app/Notifications/System/TestPushNotification.php`
- `app/Services/Billing/SubscriptionService.php`, `BetaApplicationService.php`
- `app/Services/Savings/FundSpendService.php`, `FundTransferService.php`
- `app/Http/Controllers/Teams/TeamInvitationController.php`
- `app/Console/Commands/Notifications/IncomeReminderCommand.php`, `SendTestPushCommand.php`
- `app/Http/Controllers/Admin/AdminNotificationTestController.php`
- `app/Http/Requests/Settings/UpdateNotificationPreferencesRequest.php`
- `app/Http/Requests/Admin/SendTestPushRequest.php`
- `routes/admin.php`, `routes/console.php`

### Frontend
- `resources/js/pages/settings/notifications.tsx`
- `resources/js/pages/admin/notifications-test/index.tsx`
- `resources/js/types/notifications.ts`
- `resources/js/lib/admin-nav.ts`

### Tests
- `tests/Feature/Settings/NotificationPreferencesTest.php`
- `tests/Feature/Notifications/SendTestPushCommandTest.php`
- `tests/Feature/Notifications/SubscriptionPastDueNotificationTest.php`
- `tests/Feature/Notifications/GranularPushNotificationTest.php`
- `tests/Feature/Notifications/IncomeReminderCommandTest.php`
- `tests/Pest.php` (`createUserWithPlan` helper)

## Deploy / verify

```bash
php artisan migrate
php artisan wayfinder:generate --no-interaction
vendor/bin/pint --dirty
npm run build
```

Ensure `queue:work` runs in production (all notification classes use `ShouldQueue`).

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Settings/NotificationPreferencesTest.php
php artisan test --compact tests/Feature/Notifications/
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`) if frontend changed

### Happy path

1. Log in as a member with push enabled
2. Open **Settings → Notification settings**
3. Confirm per-event push toggles and payday field; save
4. Enable browser push on the device
5. As platform admin, open **Admin → Push test** → send to self → confirm OS notification + bell entry
6. Invite a registered user to a team → invitee gets push (if `push_team_invitations` on)
7. Toggle off team invitation push → re-invite → no push, inbox still works if in-app on

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] All push toggles persist after save
- [ ] Payday field accepts 1–28 only
- [ ] Admin push test shows subscriber count

### Regression

- [ ] Pending spend/transfer push still works
- [ ] Trial ending reminder unchanged
- [ ] Login/logout still works

## Suggested application commit

```
Summary: Add granular push prefs and significant-action notifications

Per-event Web Push toggles, new notification kinds (past due, invitation accepted,
action confirmed, income reminder, beta approved), admin test push tooling, and
payday-based income reminder command.
```

## Linear paste block

```
Title: Significant-action push notifications

Description:
Shipped granular per-event Web Push preferences, Web Push on team invitations and
low fund balance, and new notifications for subscription past due, invitation
accepted, pending action confirmed, income reminders, and beta approval. Platform
admins can send test push from Admin → Push test or notifications:send-test-push.

Comment / instructions:
Run php artisan migrate after deploy. Visual QA: Settings → Notification settings
→ per-event toggles; Admin → Push test. Suggested: php artisan test --compact tests/Feature/Notifications/
```
