# Category-to-Category Transfers

**Date:** 2026-08-01

## Summary

Transfers now move savings **between funds (categories)**, not from a fund into a bank deposit. When source and destination funds use different assigned banks, the UI shows a reminder modal and the transfer stays pending until confirmed. Same-bank transfers auto-confirm and update balances immediately.

## Changelog

- `fund_transfers` schema: `from_category_id`, `to_category_id`, `from_bank_id`, `to_bank_id` (snapshotted from category assignments)
- Fund remaining: `allocated − transferred out + received − spent`
- Same bank: auto-confirmed on save; cross-bank: pending until user confirms funds moved
- Transfers page: From fund / To fund selects, bank reminder dialog, Received column on balance cards
- Bank balances: category drill-down reflects in/out per confirmed transfer

## Files touched

**Backend:** migration `2026_07_31_195342_refactor_fund_transfers_for_category_to_category.php`, `FundTransfer` model, `FundTransferService`, `FundTransferController`, `SaveFundTransferRequest`, `FundBalanceService`, `SavingsCategory`, `Bank`, factory

**Frontend:** `resources/js/pages/savings/transfers/index.tsx`, `resources/js/types/savings.ts`

**Tests:** `FundTransferTest.php`, `FundBalanceServiceTest.php`

## Deploy steps

```bash
php artisan migrate
npm run dev
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundTransferTest.php
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`, locked income, banks assigned on Savings Plan

### Same bank

1. Assign Everyday Fund and Savings to the same bank on **Savings Plan**
2. Open **Transfers** → From Everyday Fund → To Savings → enter amount → **Record transfer**
3. Confirm no modal; transfer shows as confirmed; Everyday remaining drops, Savings received increases

### Different banks

1. Assign funds to different banks
2. Record transfer → modal reminds to move funds from source bank to destination bank
3. Confirm **I'll move the funds — record transfer**; row stays pending with **Confirm** button
4. After moving money in your bank app, click **Confirm**; balances update

### Checks

- [ ] No console errors
- [ ] Recent transfers list shows `From → To` and bank names when cross-bank
- [ ] **Banks** page category breakdown shifts with confirmed transfers

## Suggested commit

```
Summary: Make transfers category-to-category with bank reminder modal

Transfers move between savings funds. Same-bank moves auto-confirm; cross-bank
transfers stay pending until the user confirms actual funds moved between banks.
```

## Category-to-category transfers

- Transfers are fund → fund, not fund → bank deposit
- Modal appears when assigned banks differ; same bank records immediately
- Balance cards show Transferred out and Received per fund
