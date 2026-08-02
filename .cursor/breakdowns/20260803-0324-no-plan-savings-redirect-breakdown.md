# Fix savings pages error when no plan is chosen

**Date:** 2026-08-03

## Summary

Users without a savings plan were hitting HTTP 404 when visiting Income, Transfers, Spending, or Reports. Added `EnsureSavingsPlan` middleware that redirects to the Savings Plan chooser with a flash message instead of aborting.

## Changelog

- Income, Transfers, Spending, and Reports redirect to **Savings Plan** when no plan exists
- Plan chooser shows a destructive alert when redirected (`Choose a savings plan before continuing.`)
- Banks and Recipients pages unchanged — still accessible before choosing a plan

## Files touched

### Backend
- `app/Http/Middleware/EnsureSavingsPlan.php` (new)
- `bootstrap/app.php` — register `savings.plan.required` alias
- `routes/savings.php` — apply middleware to income, spending, transfers, reports
- `app/Http/Controllers/Savings/IncomePeriodController.php`
- `app/Http/Controllers/Savings/FundTransferController.php`
- `app/Http/Controllers/Savings/FundSpendController.php`
- `app/Http/Controllers/Savings/SavingsReportController.php`

### Frontend
- `resources/js/pages/savings/plan.tsx` — flash error alert

### Tests
- `tests/Feature/Savings/SavingsPlanRequiredTest.php` (new)

## Deploy / verify

- No migrations
- `php artisan wayfinder:generate --no-interaction` (routes changed)
- `vendor/bin/pint --dirty` (already run)
- `npm run dev` or `npm run build` if frontend not hot-reloading

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/SavingsPlanRequiredTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend changed

### Happy path

1. Log in as a user with no savings plan (factory user, vault unlocked)
2. Click **Income**, **Transfers**, **Spending**, or **Reports** in the sidebar
3. Confirm redirect to **Savings Plan** with red alert: "Choose a savings plan first"
4. Pick a formula and save — revisit each page; confirm they load normally

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] No raw 404 page inside the app shell
- [ ] Light and dark mode on plan chooser alert
- [ ] Dashboard setup checklist links to income/spending redirect safely

### Regression

- [ ] **Savings Plan** chooser still loads when visited directly
- [ ] **Banks** and **Recipients** still work without a plan

## Suggested commit

```
Summary: Redirect savings pages to plan chooser when no plan exists

Income, Transfers, Spending, and Reports returned 404 for users who had not
chosen a savings formula yet. Add EnsureSavingsPlan middleware to redirect
to the plan chooser with a flash message instead.
```

## Linear paste block

```
Title: Redirect savings pages to plan chooser when no plan exists

Description:
Users without a savings plan no longer hit 404 on Income, Transfers, Spending, or Reports. EnsureSavingsPlan middleware redirects to the Savings Plan chooser with a flash error. Plan page shows the alert on redirect.

Comment / instructions:
Run wayfinder generate after deploy. Visual QA: log in without a plan → sidebar Income/Transfers/Spending/Reports → confirm redirect + alert on plan page. Suggested: php artisan test --compact tests/Feature/Savings/SavingsPlanRequiredTest.php
```
