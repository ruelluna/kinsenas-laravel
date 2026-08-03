# Auto-allocate income and allow deletion — breakdown

**Date:** 2026-08-04

## Summary

Removed the manual income lock step. New income periods auto-persist fund allocations on save, enabling spending/transfers immediately. Users can delete income periods (with balance guards). Custom deduction amounts on income detail still work and recalculate allocations on save.

## Changelog

- Income auto-allocates to fund buckets when saved (no Lock button)
- Delete income period from index and detail (confirmation dialog)
- Custom deduction edits recalculate allocations
- Dashboard setup checklist no longer includes “Lock income”
- `canDrawFromFunds` replaces `hasLockedIncome` in Inertia props
- Backfill command: `php artisan savings:allocate-unlocked-income`

## Files touched

### Backend

- `app/Services/Savings/IncomeCalculationService.php` — `persistAllocations`, auto-create, delete, custom recalc
- `app/Services/Savings/FundBalanceService.php` — `assertCanRemovePeriod`, all allocations counted
- `app/Http/Controllers/Savings/IncomePeriodController.php` — destroy, removed lock/unlock
- `app/Models/SavingsPlan.php` — `canDrawFromFunds` uses `hasIncomePeriod`
- `app/Services/Dashboard/DashboardSummaryService.php` — removed lock step
- `app/Support/Marketing/ActivationGhlTagGuard.php` — first income = income-locked tag
- `app/Console/Commands/Savings/AllocateUnlockedIncomeCommand.php`
- Controllers: `FundSpendController`, `FundTransferController`, `SavingsPlanController`
- `routes/savings.php`

### Frontend

- `resources/js/components/savings/delete-income-modal.tsx` (new)
- `resources/js/components/savings/add-income-modal.tsx`
- `resources/js/pages/savings/income/index.tsx`, `show.tsx`
- `resources/js/pages/dashboard.tsx`
- `resources/js/pages/savings/spending/index.tsx`, `transfers/index.tsx`, `plan.tsx`, `reports.tsx`
- `resources/js/components/dashboard/summary-stat-cards.tsx`
- `resources/js/components/savings/add-fund-balance-modal.tsx`
- `resources/js/types/savings.ts`, `dashboard.ts`

### Tests

- `tests/Feature/Savings/IncomePeriodTest.php`
- `tests/Feature/Savings/FundSpendTest.php`
- `tests/Feature/Savings/FundBalanceServiceTest.php`
- `tests/Feature/Savings/FundTransferTest.php`
- `tests/Feature/DashboardTest.php`
- `tests/Pest.php`

## Deploy steps

```bash
php artisan savings:allocate-unlocked-income
php artisan wayfinder:generate --no-interaction
npm run dev
# or npm run build
```

Run the backfill command once if any unlocked income periods exist in production.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
php artisan test --compact tests/Feature/Savings/FundSpendTest.php
php artisan test --compact tests/Feature/DashboardTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Happy path

1. Log in as a subscribed user with a savings plan
2. Open **Income** → **Add income** → confirm fund balances update without Lock
3. Open income detail → confirm breakdown shows allocated amounts
4. Edit custom deductions (if plan has them) → save → confirm allocations recalculate
5. Delete an income period with no spending → confirm removed
6. Add income, record spending, try delete → confirm blocked with error
7. Dashboard → no “Lock income” setup step

### Checks

- [ ] No console errors
- [ ] Light and dark mode
- [ ] Mobile ~375px layout usable

### Regression

- [ ] Spending/transfers enabled after income or opening balance
- [ ] Login/logout

## Suggested application commit

```
Summary: Auto-allocate income on save and allow deletion

Income periods persist fund allocations immediately when saved, removing the
manual lock step. Users can delete income periods when balances allow; custom
deduction edits recalculate allocations.
```

## Implementation summary

- Auto-allocate income on save; lock/unlock routes and UI removed
- Delete income with balance guard (same rules as former unlock)
- Custom amounts recalculate allocations on save
- Dashboard and copy updated; `canDrawFromFunds` prop replaces `hasLockedIncome`
- Backfill command for legacy unlocked periods

Visual QA: see breakdown above.
