# Fund spending and running balances

**Date:** 2026-08-01

## Summary

Replaced per-paycheck Transfers with plan-scoped **Spending** and **running fund balances** across all locked income. Users record daily expenses from Everyday Fund, car repairs from Empower Fund, etc., with allocated / spent / remaining visible on the Spending page, Income detail, Savings Plan, and Reports.

## Changelog

- New **Spending** sidebar item (legacy `/transfers` redirects)
- Fund balance cards per category with Everyday Fund as default quick-spend target
- Quick spend form: fund, amount, description, date; optional bank/recipient (pending until confirmed)
- Overspend validation blocks amounts above remaining balance
- Income detail shows running **Remaining** column + link to Spending
- Savings Plan shows fund balance summary when income is locked
- Reports **Fund health** table replaces by-category transfer totals
- Unlock income blocked when confirmed spending would exceed post-unlock allocation

## Files touched

**Backend:** `fund_spends` migration, `FundSpend` model/factory, `FundBalanceService`, `FundSpendService`, `FundSpendController`, `SaveFundSpendRequest`, routes; removed Transfer model/controller/service; updated `IncomeCalculationService`, `SavingsReportController`, `IncomePeriodController`, `SavingsPlanController`, related models

**Frontend:** `savings/spending/index.tsx`, `types/savings.ts`, `app-sidebar.tsx`, `income/show.tsx`, `reports.tsx`, `plan.tsx`

**Tests:** `FundSpendTest.php`, `FundBalanceServiceTest.php`

## Deploy

```bash
php artisan migrate
npm run dev   # or npm run build
vendor/bin/pint --dirty
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundSpendTest.php
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend changed; TRC or Abundant plan with locked income

### Happy path

1. Log in → **Spending** in sidebar
2. Confirm Everyday Fund shows allocated / spent / remaining
3. Record ₱500 from Everyday Fund with description "Groceries"
4. Record ₱5,000 from Empower Fund — "Car repair"
5. Open **Income** → detail → **Remaining** column updates
6. Open **Reports** → **Fund health** table
7. Try overspend → validation error

### Checks

- [ ] No console errors
- [ ] Light and dark mode on Spending page
- [ ] `/savings/transfers` redirects to Spending

## Suggested commit

```
Summary: Add fund spending with running balances across locked income

Replace Transfers with Spending: record expenses from category pools (Everyday,
Empower, etc.) with allocated/spent/remaining tracking, overspend validation,
and fund health reports.
```
