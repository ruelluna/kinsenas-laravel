# GHL tag lifecycle — breakdown

**Date:** 2026-08-02 22:34

## Summary

Refactored GoHighLevel sync to use incremental add/remove tag APIs instead of overwriting tags on upsert. Wired tag mutations across beta status transitions, registration, email verification, banks, activation milestones, billing, and teams.

## Changelog

- GHL upsert now creates/updates contacts **without** tags; tags are added via `POST /contacts/{id}/tags` and removed via `DELETE /contacts/{id}/tags`
- Beta approve adds `beta-approved` and removes `beta-pending` / `beta-rejected`; reject does the inverse
- Registration adds `kinsenas-user`, `registered`; email verify adds `email-verified` (+ `beta-launch-discount-eligible` when eligible)
- Bank add/remove syncs `bank-added`, `{slug}-bank-added`, and `gotyme-gosave-setup` based on team holdings
- Activation milestones: `plan-created`, `first-income-entered`, `income-locked`, `activated-user`, `first-transfer`, `first-spend`, `vault-unlocked`
- Billing/teams: `payment-submitted`, `subscription-active`, `trial-active`, `subscription-cancelled`, `team-invite-sent`, `team-member`, `team-created`, `beta-feedback-*`

## Files touched

### Marketing / GHL
- `app/Services/Marketing/GhlMarketingService.php`
- `app/Services/Marketing/GhlUserTagService.php`
- `app/Services/Marketing/BankGhlTagService.php`
- `app/Services/Marketing/ActivationGhlTagService.php`
- `app/Support/Marketing/GhlTagCatalog.php`
- `app/Support/Marketing/BankGhlTagResolver.php`
- `app/Support/Marketing/ActivationGhlTagGuard.php`
- `app/Jobs/SyncUserTagsToGhl.php`

### Integration points
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Listeners/GrantBetaLaunchDiscountOnVerified.php`
- `app/Http/Controllers/Savings/BankController.php`
- `app/Http/Controllers/Savings/SavingsPlanController.php`
- `app/Http/Controllers/Savings/IncomePeriodController.php`
- `app/Http/Controllers/Savings/FundTransferController.php`
- `app/Http/Controllers/Savings/FundSpendController.php`
- `app/Http/Controllers/Vault/VaultUnlockController.php`
- `app/Http/Controllers/Settings/BetaFeedbackController.php`
- `app/Http/Controllers/Billing/PaymentSubmissionController.php`
- `app/Http/Controllers/Admin/AdminPaymentSubmissionController.php`
- `app/Services/Billing/SubscriptionService.php`
- `app/Http/Controllers/Teams/TeamInvitationController.php`
- `app/Actions/Teams/CreateTeam.php`

### Tests
- `tests/Pest.php`
- `tests/Feature/OpenBeta/OpenBetaRegistrationTest.php`
- `tests/Feature/SurveyResponseStoreTest.php`
- `tests/Feature/Marketing/GhlTagSyncTest.php`
- `tests/Feature/Savings/BankGhlTagTest.php`

## Deploy / verify

1. Confirm GHL Private Integration token has contact create/edit + tag add/remove scopes
2. `GHL_ENABLED=true`, `GHL_PIT`, `GHL_LOCATION_ID` in environment
3. Queue worker required when `QUEUE_CONNECTION` is not `sync`
4. `vendor/bin/pint --dirty`

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/OpenBeta/OpenBetaRegistrationTest.php
php artisan test --compact tests/Feature/SurveyResponseStoreTest.php
php artisan test --compact tests/Feature/Marketing/GhlTagSyncTest.php
php artisan test --compact tests/Feature/Savings/BankGhlTagTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

N/A — no UI change.

## Suggested application commit

```
Summary: Add incremental GHL tag sync across user lifecycle

Replace upsert tag overwrites with add/remove tag APIs so beta, survey,
bank, activation, and billing events accumulate tags on the same contact.
```
