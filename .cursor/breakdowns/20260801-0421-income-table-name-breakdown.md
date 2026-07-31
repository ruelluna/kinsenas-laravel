# Income table and name field

**Date:** 2026-08-01

## Summary

Added a required **Income name** field when creating income periods. Replaced the Income index card list with a table showing Date, Income name, Amount, one column per plan category (header includes percentage), and a totals row. Income detail page now shows the name in the title and breadcrumbs.

## Changelog

- **Add income** modal includes an **Income name** field (required).
- **Income** index is a scrollable table with plan category columns labeled with percentages (e.g. `Everyday Fund (70.00%)`).
- Each row shows per-category allocated amounts; footer row totals income and category amounts.
- Income detail page title uses the income name instead of the date alone.

## Files touched

### Backend
- `database/migrations/2026_07_31_202328_add_name_to_income_periods_table.php`
- `app/Models/IncomePeriod.php`
- `app/Http/Requests/Savings/SaveIncomePeriodRequest.php`
- `app/Services/Savings/IncomeCalculationService.php`
- `app/Http/Controllers/Savings/IncomePeriodController.php`

### Frontend
- `resources/js/types/savings.ts`
- `resources/js/components/savings/add-income-modal.tsx`
- `resources/js/pages/savings/income/index.tsx`
- `resources/js/pages/savings/income/show.tsx`

### Tests
- `tests/Feature/Savings/IncomePeriodTest.php`
- `tests/Feature/Savings/FundBalanceServiceTest.php`
- `tests/Feature/Savings/FundTransferTest.php`
- `tests/Feature/Savings/FundSpendTest.php`
- `tests/Feature/Savings/SavingsPlanTest.php`

## Deploy steps

```bash
php artisan migrate
npm run dev
# or npm run build
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend not already running; `php artisan migrate`

### Happy path

1. Log in as a user with a savings plan.
2. Open **Income** in the sidebar.
3. Click **Add income** — enter **Income name**, **Period start**, and **Amount** → **Save income**.
4. Confirm the new row appears in the table with Date, name, amount, and category columns.
5. Confirm the **Total** footer row sums amount and category columns.
6. Click the date link — detail page shows the income name as the title.

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Category headers show plan percentages
- [ ] Locked badge appears on locked rows
- [ ] Lock / Unlock buttons still work from the table
- [ ] Mobile width (~375px): table scrolls horizontally

### Regression

- [ ] Income lock/unlock and show breakdown still work
- [ ] Spending and transfers still respect locked income

## Suggested application commit

```
Summary: Add income name and category table on Income index

Income periods now require a name at creation. The Income index lists all
periods in a table with plan category columns, allocated amounts, and totals.
```

## Implementation summary (paste)

## Income name and category table

- Add income modal requires an **Income name** field.
- Income index shows a table: Date, Income name, Amount, plan category columns (with %), and totals row.
- Income detail page uses the income name in the title and breadcrumbs.
- Run `php artisan migrate` after deploy.
