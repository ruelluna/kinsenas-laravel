# Fund card bank badge — breakdown

**Date:** 2026-08-03

## Summary

Linked banks now appear on every fund surface: compact/detailed fund cards, Dashboard and Savings plan Fund balances sections, income breakdown rows, and reports fund health table. Transfers page inline cards were refactored to use `FundBalanceGrid`.

## Changelog

- Fund cards show institution logo (or letter fallback) and bank display name top-right when a fund has an assigned bank
- Default fund badge stacks below the bank badge when both apply
- Dashboard and Savings plan share `FundBalancesSection` for the Fund balances shell
- Transfers uses `FundBalanceGrid` with Received row and Transfer action (removed duplicate markup)
- Income period breakdown and Reports fund health table show inline bank badge in Category/Fund column
- `FundBalance` and report `fund_health` payloads include bank metadata from category assignment

## Files touched

### Backend
- `app/Services/Savings/FundBalanceService.php`

### Frontend
- `resources/js/types/savings.ts`
- `resources/js/components/savings/fund-bank-badge.tsx` (new)
- `resources/js/components/savings/fund-card-header.tsx` (new)
- `resources/js/components/savings/fund-balances-section.tsx` (new)
- `resources/js/components/savings/fund-balance-grid.tsx`
- `resources/js/pages/dashboard.tsx`
- `resources/js/pages/savings/plan.tsx`
- `resources/js/pages/savings/transfers/index.tsx`
- `resources/js/pages/savings/income/show.tsx`
- `resources/js/pages/savings/reports.tsx`

### Tests
- `tests/Feature/Savings/FundBalanceServiceTest.php`
- `tests/Feature/DashboardTest.php`
- `tests/Feature/Savings/FundSpendTest.php`

## Deploy / migration

None.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Feature/DashboardTest.php
php artisan test --compact tests/Feature/Savings/FundSpendTest.php
php artisan test --compact tests/Feature/Savings/FundTransferTest.php

vendor/bin/pint --dirty
npm run dev
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend not running

### Happy path

1. Log in as a user with a savings plan and locked income
2. Open **Savings plan** → assign banks to funds via category bank dropdown → save
3. Open **Dashboard** → confirm linked fund cards show bank icon + label top-right
4. On **Savings plan**, confirm Fund balances section matches
5. Open **Spending** and **Transfers** → detailed cards show bank badge
6. Open an **Income** period → breakdown table Category column shows inline bank badge
7. Open **Reports** → Fund health table Fund column shows inline bank badge

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Unlinked funds show no bank badge
- [ ] Default fund shows bank badge + Default badge stacked
- [ ] Mobile width (~375px): labels truncate, no overlap

### Regression

- [ ] Transfers: Transfer from button and Received row still work
- [ ] Dashboard quick links (Savings Plan, Income, Reports) still visible

## Suggested application commit

```
Summary: Show linked bank badge on fund cards and tables

Fund balances now include bank metadata from category assignments. Cards,
income breakdown, and reports fund health display the bank icon and label
when a fund is linked. Transfers reuses FundBalanceGrid; Dashboard and
Plan share FundBalancesSection.
```
