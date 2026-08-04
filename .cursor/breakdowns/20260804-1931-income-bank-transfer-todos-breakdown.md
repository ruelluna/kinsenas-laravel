# Income bank transfer todos — breakdown

**Date:** 2026-08-04

## Summary

When income is entered or re-allocated, Kinsenas now generates per-fund-bucket transfer todos showing how much to move to each category’s assigned bank. Members mark items complete after moving money in their banking app. This is a checklist only — fund allocations and balances behave as before.

## Changelog

- New `income_distribution_todos` table and `IncomeDistributionTodo` model (pending / completed)
- `IncomeDistributionTodoService` syncs todos from allocations on income create and custom-amount updates
- Income detail page: **Move to your banks** checklist with Mark complete actions
- Income index: pending transfer badge per period
- Completing a todo reopens if that bucket’s allocation amount changes

## Files touched

### Backend

- `database/migrations/2026_08_04_113201_create_income_distribution_todos_table.php`
- `app/Enums/IncomeDistributionTodoStatus.php`
- `app/Models/IncomeDistributionTodo.php`
- `app/Models/IncomePeriod.php`
- `database/factories/IncomeDistributionTodoFactory.php`
- `app/Services/Savings/IncomeDistributionTodoService.php`
- `app/Services/Savings/IncomeCalculationService.php`
- `app/Http/Controllers/Savings/IncomePeriodController.php`
- `app/Http/Requests/Savings/CompleteIncomeDistributionTodoRequest.php`
- `routes/savings.php`

### Frontend

- `resources/js/types/savings.ts`
- `resources/js/components/savings/income-distribution-todos.tsx`
- `resources/js/pages/savings/income/show.tsx`
- `resources/js/pages/savings/income/index.tsx`

### Tests

- `tests/Feature/Savings/IncomeDistributionTodoTest.php`

## Deploy steps

```bash
php artisan migrate
php artisan wayfinder:generate --no-interaction
npm run dev
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/IncomeDistributionTodoTest.php
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`, `php artisan migrate`

### Happy path

1. Log in as a member with a savings plan and at least one bank assigned on a fund bucket
2. Open **Savings → Income** → add income
3. Open the income period detail
4. Confirm **Move to your banks** lists one row per fund bucket with amounts and bank badges
5. Click **Mark complete** on a row → toast and row shows confirmed state
6. On **Income** index, confirm **N transfers pending** badge until all are complete

### Checks

- [ ] No console errors
- [ ] Categories without assigned bank show link to plan page; can still mark complete
- [ ] Saving custom deduction amounts reopens affected completed todos when amount changes
- [ ] Deleting income removes todos (no orphans)

## Suggested commit

```
Summary: Add bank transfer todos on income entry

Members get a per-fund checklist on each income period to track real-world
transfers to assigned banks. Completing items is confirmation-only; allocations
stay auto-applied on save.
```
