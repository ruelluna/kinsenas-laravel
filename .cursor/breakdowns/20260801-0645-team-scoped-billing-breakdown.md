# Team-scoped billing

**Date:** 2026-08-01

## Summary

Subscriptions moved from users to teams (finance workspaces). Personal teams get a register trial; shared teams require immediate subscription (past due). Billing and lockout are per current team; only team owners can submit payment.

## Changelog

- Migration: `subscriptions.team_id` (unique), `payment_submissions.team_id` with backfill
- `SubscriptionService` refactored for team-scoped trial, activation, and access checks
- Personal team: 14-day trial on register; shared team: `past_due` on create
- Middleware resolves subscription by route team or `currentTeam`; profile/teams/billing stay reachable when locked out
- Billing page scoped to current team; owner-only payment submission (`TeamBillingPolicy`)
- Admin subscribers list/manage teams instead of users
- Register copy clarifies trial is for personal finance workspace
- Teams index shows subscription status per workspace

## Files touched

### Database
- `database/migrations/2026_07_31_224048_move_subscriptions_to_teams.php`

### Backend
- `app/Models/Subscription.php`, `Team.php`, `User.php`, `PaymentSubmission.php`
- `app/Services/Billing/SubscriptionService.php`
- `app/Actions/Teams/CreateTeam.php`, `app/Actions/Fortify/CreateNewUser.php`
- `app/Policies/TeamBillingPolicy.php`
- `app/Http/Middleware/EnsureSubscribedOrTrialing.php`, `HandleInertiaRequests.php`
- `app/Http/Controllers/Settings/BillingController.php`, `Billing/PaymentSubmissionController.php`
- `app/Http/Controllers/Admin/AdminSubscriberController.php`, `AdminPaymentSubmissionController.php`, `AdminPlatformUserController.php`
- `app/Http/Controllers/Teams/TeamController.php`
- `app/Http/Responses/Concerns/RedirectsToCurrentTeam.php`
- `routes/settings.php`, `routes/admin.php`
- Factories: `SubscriptionFactory.php`, `PaymentSubmissionFactory.php`, `UserFactory.php`

### Frontend
- `resources/js/pages/auth/register.tsx`
- `resources/js/pages/settings/billing.tsx`
- `resources/js/pages/teams/index.tsx`
- `resources/js/pages/admin/subscribers/index.tsx`, `show.tsx`
- `resources/js/components/app-sidebar.tsx`
- `resources/js/layouts/settings/layout.tsx`
- `resources/js/types/billing.ts`, `teams.ts`

### Tests
- `tests/Feature/Auth/RegistrationTest.php`
- `tests/Feature/Billing/TrialExpiredAccessTest.php`, `TeamBillingTest.php`
- `tests/Feature/Billing/SubscriptionFeatureTest.php`, `SyncSubscriptionStatusCommandTest.php`
- `tests/Feature/Admin/SubscriberAdminTest.php`, `PaymentSubmissionAdminTest.php`

## Deploy steps

```bash
php artisan migrate
npm run dev
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Auth/RegistrationTest.php
php artisan test --compact tests/Feature/Billing/TrialExpiredAccessTest.php
php artisan test --compact tests/Feature/Billing/TeamBillingTest.php
php artisan test --compact tests/Feature/Admin/SubscriberAdminTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

1. Register — confirm trial copy mentions **personal finance workspace**
2. Use app on personal team during trial — full access
3. Create a **second team** — redirected to billing; status past due
4. As team **member** (not owner) on unpaid team — billing shows owner-only message, no pay buttons
5. Switch teams via sidebar when one team is expired but another is active — active team works
6. Admin → **Subscribers** — lists teams with owner email

## Suggested commit

```
Summary: Move billing to team workspaces with owner-only payment

Charge per finance team instead of per user: personal team gets register
trial, shared teams require subscription, and lockout follows current team.
```
