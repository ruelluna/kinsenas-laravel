# Category–Bank Assignments and Fund Transfers

**Date:** 2026-08-01

## Summary

Restored fund transfers as a separate flow from spending, added many-to-many category–bank assignments, wired the Philippine `BankInstitution` catalog into bank creation, and compute per-bank balances (with per-category breakdown). Remaining balance is now **allocated − transferred − spent**.

## Changelog

- New `fund_transfers` table and Transfers page (sidebar between Income and Spending)
- Category–bank pivot `bank_savings_category`; assign banks per fund on Savings Plan
- `BankInstitutionPicker` on Banks page; bank rows show logos and net balances
- Fund balance cards show Allocated / Transferred / Spent / Remaining
- Reports: Transferred column in fund health; By bank shows net balance with category drill-down
- Spending bank select filtered by category assignments when assignments exist

## Files touched

**Backend:** migration, `FundTransfer` model/factory/service/controller/request, extended `FundBalanceService`, `SavingsPlanService` pivot sync, updated `Bank`/`SavingsCategory` models, `BankController`, `FundSpendController`, `SavingsPlanController`, `routes/savings.php`

**Frontend:** `bank-institution-picker`, `category-bank-select`, `bank-select`, `transfers/index`, updated `banks/index`, `spending/index`, `plan.tsx`, `reports.tsx`, `app-sidebar.tsx`, `types/savings.ts`

**Tests:** `FundTransferTest`, updates to `FundBalanceServiceTest`, `FundSpendTest`, `SavingsPlanTest`

## Deploy steps

```bash
php artisan migrate
php artisan wayfinder:generate --no-interaction
npm run dev
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundTransferTest.php
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Feature/Savings/FundSpendTest.php
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`, `php artisan db:seed --class=PhilippineBankSeeder` if institutions empty

1. Log in, open **Banks** → add BDO via institution picker; confirm logo and row
2. **Savings Plan** → assign banks to Emergency Fund; save
3. Lock income, open **Transfers** → record transfer to fund + bank; confirm; remaining drops
4. **Spending** → spend from same fund; remaining drops again
5. **Banks** → net balance with per-fund breakdown
6. **Reports** → Transferred column + By bank nested categories

## Suggested commit

```
Summary: Add category–bank assignments and fund transfers

Transfers are separate from spending with remaining = allocated − transferred − spent.
Categories assign banks on the plan page; Banks and Reports show per-bank balances.
```

## Changelog (2026-08-01 follow-up)

- `FundBalanceService::bankBalancesForTeam()` now seeds per-bank breakdown from **assigned categories** (`savings_categories.bank_id`), not only categories with transfer/spend activity
- Assigned categories appear at `₱0.00` before any confirmed activity; net per category remains transfers in minus spends out on that bank
- **Fix:** assigned categories now show each fund's **remaining allocation** (`allocated − transferred + received − spent`), not transfer/spend net on the bank
- Legacy activity on unassigned categories still included at end of breakdown

**Tests:** `FundBalanceServiceTest` (assigned categories at zero), `FundTransferTest` (bank assignment in balance test)
