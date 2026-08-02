# Fund Buckets visual rename

**Date:** 2026-08-03

## Summary

Renamed member-facing savings copy from **category/categories/fund** to **Fund bucket / Fund buckets** across the Inertia app, validation messages, seeded plan guidance defaults, marketing FAQ, onboarding tour, and survey — without changing models, routes, or form field names.

## Changelog

- **Savings Plan:** Card titles, descriptions, alerts, dialogs, and buttons use fund bucket wording
- **Income (period):** Custom fund buckets section; breakdown column **Fund bucket**
- **Spending / Transfers:** Dropdown labels **Fund bucket**, **From fund bucket**, **To fund bucket**
- **Reports / Banks / Dashboard:** Table columns and stat card copy aligned
- **Admin formula templates:** Fund bucket descriptions heading and helper text
- **Validation errors:** Plan save, spend, and transfer messages reference fund buckets
- **Seeder defaults:** `SavingsPlanPageGuidanceSeeder` updated for fresh installs
- **Marketing / tour / survey:** Landing FAQ, onboarding steps, survey EN/TL/CEB updated
- **Unchanged:** Beta feedback **Category**; code identifiers (`category_id`, `SavingsCategory`); aggregate **Fund balances** / **Fund health**

## Files touched

### Frontend (`resources/js/`)

- `pages/savings/plan.tsx`, `income/show.tsx`, `transfers/index.tsx`, `reports.tsx`, `banks/index.tsx`
- `components/savings/plan-guidance-panels.tsx`, `category-bank-select.tsx`, `add-spending-modal.tsx`, `edit-spending-modal.tsx`, `add-transfer-modal.tsx`, `add-bank-modal.tsx`
- `components/dashboard/summary-stat-cards.tsx`
- `pages/admin/formula-templates/index.tsx`, `edit.tsx`
- `components/marketing/landing-content.ts`
- `lib/onboarding-tour/steps.ts`, `lib/survey/survey-content.ts`

### Backend

- `app/Services/Savings/SavingsPlanService.php`
- `app/Services/Savings/FundTransferService.php`
- `app/Http/Requests/Savings/SaveSavingsPlanRequest.php`, `SaveFundTransferRequest.php`
- `app/Http/Controllers/Savings/FundSpendController.php`, `FundTransferController.php`
- `database/seeders/SavingsPlanPageGuidanceSeeder.php`

## Deploy / migration

- No migration
- **Existing DB:** `SavingsPlanPageGuidanceSeeder` uses `firstOrCreate` — guidance text already in DB is **not** auto-updated. Update via **Admin → Savings plan guidance** or edit the singleton row locally
- `npm run dev` or `npm run build` for frontend changes

## Suggested tests (run manually)

```bash
# Plan validation messages (optional — assert fund bucket wording)

php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php

php artisan test --compact tests/Feature/Savings/FundTransferTest.php

vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend not hot-reloading

### Happy path

1. Log in as a user with a savings plan (e.g. demo seed)
2. Open **Savings Plan** — confirm cards say **Fund bucket 1**, **Add fund bucket**, custom fund bucket alert
3. Open **Income** → a period — **Custom fund buckets** section; table header **Fund bucket**
4. Open **Spending** → Add spend — label **Fund bucket**
5. Open **Transfers** → Add transfer — **From fund bucket** / **To fund bucket**
6. Open **Reports** and **Banks** — column **Fund bucket**
7. Dashboard with locked income — **Across all fund buckets** stat

### Checks

- [ ] No console errors
- [ ] Beta feedback form still shows **Category** (unchanged)
- [ ] Landing FAQ mentions fund buckets
- [ ] Onboarding tour steps mention fund buckets (reset tour if needed)

### Regression

- [ ] Plan save still works (percentage total 100%)
- [ ] Spend and transfer modals submit successfully

## Suggested application commit

```
Summary: Rename savings UI copy to Fund buckets

Unifies member-facing labels from category/fund to fund bucket across plan,
income, spending, transfers, reports, validation messages, and marketing.
Backend models and form fields unchanged.
```

## Linear paste block

```
Title: Rename savings UI copy to Fund buckets

Description:
Member-facing savings labels now say Fund bucket / Fund buckets instead of
category or fund. Covers plan editor, income breakdown, spend/transfer modals,
reports, banks, dashboard stats, admin formula templates, validation errors,
and marketing/onboarding/survey copy. No schema or API changes.

Comment / instructions:
Run npm run dev or npm run build. Existing plan page guidance in DB is not
overwritten by seeder — update Admin → Savings plan guidance if needed.
Visual QA: Savings Plan → Income → Spending → Transfers → Reports.
Suggested: php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
```
