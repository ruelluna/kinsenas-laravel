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

## Changelog — 2026-08-03 (Add Existing Fund on every bucket)

- **Add Existing Fund** button on every saved fund bucket card on Savings Plan (percentage and custom)
- Same button on Dashboard and plan **Fund balances** grid cards
- Funding works **any time**, including after income locks percentage splits
- Plan save form still blocks opening-balance edits after income; dedicated PATCH adds funds additively
- Custom deduction buckets support `opening_balance_encrypted` same as percentage buckets
- `canFund` true for all categories when vault is unlocked; spending UI gated by `canDrawFromFunds`

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
- `resources/js/components/savings/fund-balances-section.tsx`
- `resources/js/components/savings/add-fund-balance-modal.tsx`
- `resources/js/components/dashboard/summary-stat-cards.tsx`

- `routes/savings.php`

### Tests

- `tests/Feature/Savings/SavingsPlanTest.php`
- `tests/Feature/Savings/FundBalanceServiceTest.php`
- `tests/Feature/Savings/FundSpendTest.php`
- `tests/Feature/DashboardTest.php`

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
2. Open **Savings Plan** → pick a formula
3. Confirm **each fund bucket card** shows **Add Existing Fund**
4. Click **Add Existing Fund** on one bucket → add ₱5,000 → confirm remaining updates
5. Add income and lock percentages → **Add Existing Fund** still works on locked cards
6. Custom fund buckets also show **Add Existing Fund**
7. Dashboard fund cards show **Add Existing Fund**
8. **Record spending** enabled only after a bucket has remaining &gt; 0

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
Summary: Add Existing Fund button on every fund bucket anytime

Users can add existing savings to any fund bucket (percentage or custom) via
Add Existing Fund on plan cards and balance grids, before or after income lock.
Plan save still blocks opening-balance edits after income; PATCH endpoint adds
funds additively.
```

## Implementation summary

- Optional existing savings on plan editor; not a blocking wizard
- Encrypted per-category opening balances; editable until first income
- Fund balances, dashboard, spending, and transfers respect opening balances
