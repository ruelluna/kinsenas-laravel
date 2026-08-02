# Existing savings per fund bucket — breakdown

**Date:** 2026-08-03

## Summary

Users can optionally enter how much they already have saved in each percentage fund bucket on the savings plan editor before their first income entry. Opening balances are encrypted per category, feed into fund balance `remaining`, and unlock spending/transfers before the first income lock.

## Changelog

- Optional **Existing savings** panel on Savings Plan editor (before first income)
- Per-fund encrypted `opening_balance_encrypted` on `savings_categories`
- Fund balances visible when opening balances exist (no locked income required)
- Spending and transfers allowed when remaining &gt; 0 from opening balances
- Dashboard shows balances and quick actions when opening balances are set
- Post-create toast nudges users to add existing savings
- Opening balances lock after first income entry (server-validated)

## Files touched

### Backend

- `database/migrations/2026_08_02_183044_add_opening_balance_encrypted_to_savings_categories_table.php`
- `app/Models/SavingsCategory.php`, `app/Models/SavingsPlan.php`
- `app/Services/Savings/FundBalanceService.php`, `app/Services/Savings/SavingsPlanService.php`
- `app/Services/Dashboard/DashboardSummaryService.php`
- `app/Http/Requests/Savings/SaveSavingsPlanRequest.php`
- `app/Http/Controllers/Savings/SavingsPlanController.php`
- `app/Http/Controllers/Savings/FundSpendController.php`, `FundTransferController.php`
- `app/Http/Controllers/Savings/IncomePeriodController.php`, `BankController.php`
- `database/factories/SavingsCategoryFactory.php`

### Frontend

- `resources/js/types/savings.ts`, `resources/js/types/dashboard.ts`
- `resources/js/pages/savings/plan.tsx`
- `resources/js/pages/dashboard.tsx`
- `resources/js/pages/savings/spending/index.tsx`, `transfers/index.tsx`
- `resources/js/components/savings/fund-balance-grid.tsx`
- `resources/js/components/dashboard/summary-stat-cards.tsx`

### Tests

- `tests/Feature/Savings/SavingsPlanTest.php`
- `tests/Feature/Savings/FundBalanceServiceTest.php`
- `tests/Feature/Savings/FundSpendTest.php`

## Deploy steps

```bash
php artisan migrate
npm run dev
# or npm run build
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Feature/Savings/FundSpendTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`, vault unlocked

### Happy path

1. Log in as a subscribed user with banks added
2. Open **Savings Plan** → pick **TRC Savings** (or Abundant)
3. Confirm toast mentions adding existing savings
4. On plan editor, find **Existing savings** panel and **Already saving?** alert
5. Enter amounts (e.g. Everyday ₱25,000), **Save plan**
6. Confirm **Fund balances** shows remaining without locked income
7. Open **Spending** → record a spend → remaining decreases
8. Add first income → confirm opening balance fields are hidden
9. Attempt to change opening balances via API after income → validation error

### Checks

- [ ] No console errors
- [ ] Light and dark mode
- [ ] Mobile ~375px layout usable
- [ ] Dashboard shows balances when opening balances set (before income lock)

### Regression

- [ ] Normal flow: income lock → balances still correct
- [ ] Login/logout

## Suggested application commit

```
Summary: Add optional existing savings per fund bucket at plan setup

Users joining mid-cycle can enter encrypted opening balances per percentage
fund before first income. Balances feed remaining calculations and enable
spending before income lock; amounts lock after first income entry.
```

## Implementation summary

- Optional existing savings on plan editor; not a blocking wizard
- Encrypted per-category opening balances; editable until first income
- Fund balances, dashboard, spending, and transfers respect opening balances
