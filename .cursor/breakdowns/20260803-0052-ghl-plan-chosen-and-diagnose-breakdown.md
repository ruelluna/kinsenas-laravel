# GHL plan-chosen tags, custom plan, and diagnostics — breakdown

**Date:** 2026-08-03 00:52

## Summary

Shipped plan-specific GoHighLevel tags when members choose Abundant, TRC, or a custom savings plan; added a “Build your own” plan path; hardened GHL sync with `ghl:diagnose`, skip logging, `afterCommit()` job dispatch, and clearer `.env.example` notes.

Beta server still requires **MySQL connectivity for CLI** and a **running queue worker** (see deploy section).

## Changelog

- First plan create adds `plan-created` plus one of: `abundant-plan-chosen`, `trc-plan-chosen`, `custom-plan-chosen`; removes sibling plan-chosen tags
- New **Build your own** card on savings plan chooser → `POST plan/custom` creates empty plan for category editing
- `php artisan ghl:diagnose` reports config, billing mode, queue driver; optional `--upsert=email` smoke test
- GHL sync logs `GHL sync skipped` with reason when disabled; warns when upsert succeeds but contact ID is missing
- GHL jobs dispatch with `afterCommit()` so queued work runs only after DB transactions commit

## Files touched

### GHL / marketing
- `app/Support/Marketing/GhlTagCatalog.php`
- `app/Services/Marketing/ActivationGhlTagService.php`
- `app/Services/Marketing/GhlMarketingService.php`
- `app/Services/Marketing/GhlUserTagService.php`
- `app/Services/Billing/BetaApplicationService.php`
- `app/Console/Commands/GhlDiagnoseCommand.php`
- `app/Http/Controllers/Marketing/SurveyResponseController.php`

### Savings plan
- `app/Services/Savings/SavingsPlanService.php`
- `app/Http/Controllers/Savings/SavingsPlanController.php`
- `routes/savings.php`
- `resources/js/components/savings/plan-template-picker.tsx`

### Config
- `.env.example`

### Tests
- `tests/Feature/Marketing/PlanGhlTagTest.php`
- `tests/Feature/Marketing/GhlDiagnoseTest.php`
- `tests/Feature/Savings/SavingsPlanTest.php`

## Deploy / verify (beta server)

1. Fix MySQL for CLI — `php artisan migrate:status` must work over SSH (not `Connection refused` on `127.0.0.1:3306`)
2. Set `GHL_ENABLED=true`, `GHL_PIT`, `GHL_LOCATION_ID`; `php artisan config:clear` (and `config:cache` if used)
3. Start queue worker: `php artisan queue:work --sleep=3 --tries=3` (Supervisor in production)
4. Smoke: `php artisan ghl:diagnose --upsert=you@example.com`
5. `npm run build` if frontend not built on server (plan chooser UI changed)
6. Create tags in GHL: `abundant-plan-chosen`, `trc-plan-chosen`, `custom-plan-chosen`

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Marketing/PlanGhlTagTest.php
php artisan test --compact tests/Feature/Marketing/GhlDiagnoseTest.php
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
php artisan test --compact tests/Feature/Marketing/GhlTagSyncTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`)

### Happy path

1. Log in as approved beta user with at least one bank
2. Open **Savings Plan** (no plan yet)
3. Confirm three options: Abundant, TRC, **Build your own**
4. Choose **Build your own** → plan editor opens with empty categories
5. Add funds, save plan
6. With GHL enabled + queue worker: contact gets `custom-plan-chosen` and `plan-created`

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Template picks still create pre-filled categories
- [ ] Custom path cannot create second plan (existing guard)

### Regression

- [ ] Registration still queues GHL tags when enabled
- [ ] Survey submit still syncs tags

## Suggested application commit

```
Summary: Add plan-chosen GHL tags and custom savings plan path

Tag contacts by Abundant, TRC, or custom plan selection; add build-your-own
chooser and ghl:diagnose for ops. GHL jobs use afterCommit and log skip reasons.
```

## Linear paste block

```
Title: GHL plan-chosen tags, custom plan, and diagnostics

Description:
Members choosing a savings formula now sync plan-specific GHL tags (abundant/trc/custom) alongside plan-created. Custom plan option creates an empty plan for self-defined funds. ghl:diagnose helps verify env and API connectivity on beta.

Comment / instructions:
Fix MySQL + queue worker on beta before expecting any GHL sync. Set GHL_ENABLED=true. npm run build for chooser UI. Suggested: php artisan test --compact tests/Feature/Marketing/PlanGhlTagTest.php tests/Feature/Marketing/GhlDiagnoseTest.php
```
