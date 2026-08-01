# Open beta mode — breakdown

**Date:** 2026-08-01

## Summary

Shipped global open-beta mode via `BILLING_MODE=open_beta`: full free access, email verification required before app use, beta participant tracking for launch discounts, in-app feedback with admin inbox, and updated marketing/register/billing copy.

## Changelog

- Added `BILLING_MODE` config (`live` | `open_beta`) with launch discount percent setting
- Enforced email verification (`MustVerifyEmail`); register/login redirect unverified users to verification notice
- Open beta bypasses subscription lockout; teams get `open_beta` subscription status
- Beta enrollment on register; verified beta users get `beta_launch_discount_eligible`
- Settings **Feedback** page + admin **Beta feedback** inbox
- Landing, register, verify-email, billing, and in-app banner updated for open-beta messaging
- Payment submissions blocked during open beta

## Files touched

**Config / enums**
- `config/billing.php`, `.env.example`
- `app/Enums/BillingMode.php`, `BetaFeedbackCategory.php`, `SubscriptionStatus.php`

**Backend**
- `app/Models/User.php`, `BetaFeedback.php`
- `app/Services/Billing/SubscriptionService.php`, `BillingPlanPresenter.php`, `BetaEnrollmentService.php`
- `app/Actions/Fortify/CreateNewUser.php`, `app/Actions/Teams/CreateTeam.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Responses/*` (auth redirects)
- `app/Http/Controllers/Settings/BetaFeedbackController.php`, `BillingController.php`
- `app/Http/Controllers/Billing/PaymentSubmissionController.php`
- `app/Http/Controllers/Admin/AdminBetaFeedbackController.php`, `AdminSubscriberController.php`
- `app/Listeners/GrantBetaLaunchDiscountOnVerified.php`
- `routes/settings.php`, `routes/admin.php`
- Migrations: users beta columns, `beta_feedbacks` table

**Frontend**
- `resources/js/pages/auth/register.tsx`, `verify-email.tsx`
- `resources/js/pages/settings/billing.tsx`, `feedback.tsx`
- `resources/js/pages/admin/beta-feedback/index.tsx`
- `resources/js/components/open-beta-banner.tsx`, `app-sidebar.tsx`, `admin/admin-sidebar-nav.tsx`
- `resources/js/components/marketing/landing-hero.tsx`, `landing-final-cta.tsx`
- `resources/js/layouts/app/app-sidebar-layout.tsx`, `settings/layout.tsx`
- `resources/js/types/billing.ts`, `auth.ts`

**Tests**
- `tests/Feature/OpenBeta/*`
- `tests/Feature/Auth/RegistrationTest.php`

## Deploy steps

```bash
php artisan migrate
php artisan wayfinder:generate --no-interaction
npm run build
```

Set in production `.env`:

```
BILLING_MODE=open_beta
BILLING_OPEN_BETA_LAUNCH_DISCOUNT_PERCENT=20
```

To exit beta at launch, set `BILLING_MODE=live`. Beta users retain `beta_launch_discount_eligible` for future billing application.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/OpenBeta/
php artisan test --compact tests/Feature/Auth/RegistrationTest.php tests/Feature/Auth/EmailVerificationTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`, `BILLING_MODE=open_beta` in `.env`

### Happy path

1. Log out; open home page — hero shows **Open beta — free access**
2. **Create My Kinsenas Plan** → register shows open-beta card (not trial pricing)
3. Register → redirected to **Email verification**; verify via mail log/link
4. After verify → dashboard loads; open-beta banner visible
5. Sidebar → **Feedback** → submit feedback → success toast
6. **Settings → Billing** → free access message, no pay buttons; link to feedback
7. As platform admin → **Admin → Beta feedback** → submitted entry visible

### Checks

- [ ] Unverified user cannot open dashboard (redirects to verify email)
- [ ] Payment URL `/billing/pay` redirects with error during beta
- [ ] Shared team creation does not lock out during beta
- [ ] Light/dark mode on new pages

## Suggested commit

```
Summary: Add open beta mode with email gate, feedback, and launch discount tracking

Introduces BILLING_MODE=open_beta for free full access during beta, enforces
email verification before app use, tracks beta participants for launch
discounts, and adds user feedback plus admin inbox.
```

## Linear paste block

```
Title: Open beta mode with email verification and feedback

Description:
Shipped global open-beta billing mode. Users get full free access during beta
after email verification. Registration enrolls beta participants; verified users
lock in launch discount eligibility. In-app feedback form and admin inbox added.
Marketing and billing UI updated; payments blocked during beta.

Comment / instructions:
Run php artisan migrate. Set BILLING_MODE=open_beta in .env. Visual QA: register → verify email → dashboard → feedback → admin inbox. Suggested: php artisan test --compact tests/Feature/OpenBeta/
```

## Changelog (2026-08-01) — Beta application approval

- Registration during open beta now **applies for access** (`beta_application_status: pending`) instead of immediate access
- **`EnsureBetaApproved` middleware** blocks app/settings until admin approves
- **Admin → Beta applications** — approve/reject pending sign-ups
- **Pending / rejected** auth pages after email verification
- **GHL webhooks** on apply, approve, and reject (`GHL_WEBHOOK_*` env vars)
- Existing `beta_enrolled_at` users backfilled to **approved** on migrate
