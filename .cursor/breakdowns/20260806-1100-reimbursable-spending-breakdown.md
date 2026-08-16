# Reimbursable spending (expecting payback) — breakdown

**Date:** 2026-08-06

## Summary

Users can flag fund spending as **expecting payback**, record who will repay, log partial or full paybacks back to the same fund bucket, and close the expectation when they stop waiting. Fund balances treat paybacks as credits against effective spent totals.

## Changelog

- **Record spending:** optional “Expecting payback” checkbox + “Who will pay you back?” (recipient)
- **Spending list:** badges (Awaiting payback, Partially repaid, Paid back, Closed), filters, **Record payback** and **Stop expecting** actions
- **Fund balances:** remaining reflects payback credits; subline shows amount awaiting payback per fund
- **Dashboard:** awaiting payback count in summary; pending actions panel links to Spending
- **API / mobile:** reimbursement fields on spends; POST payback and close-reimbursement endpoints; mobile list shows status + quick payback

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| vs bank `pending` | Separate reimbursement ledger | Bank confirm unchanged |
| vs Tier 2 debt | Child `fund_spend_reimbursements` rows | No debt module |
| Partial payback | Supported in v1 | Multiple payback rows per spend |
| Payback destination | Same fund as spend | No cross-fund payback in v1 |

## Files touched

**Backend:** migrations, `FundSpend`, `FundSpendReimbursement`, `ReimbursementStatus`, `FundSpendReimbursementService`, `FundBalanceService`, `FundSpendService`, controllers, routes, `FundSpendResource`, `DashboardSummaryService`

**Frontend:** `add-spending-modal`, `edit-spending-modal`, `record-payback-modal`, `spending/index`, `fund-balance-grid`, dashboard pending panel + stat cards, TS types

**Shared / mobile:** `packages/shared`, `packages/api-client`, `apps/mobile/.../spending.tsx`

**Tests:** `FundSpendReimbursementTest`, `FundSpendTest`, `DashboardTest`, `SpendingApiTest`, `SpendingReimbursementTest` (browser)

## Deploy

```bash
php artisan migrate
npm run dev   # or npm run build if frontend changed
vendor/bin/pint --dirty
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundSpendReimbursementTest.php
php artisan test --compact tests/Feature/Savings/FundSpendTest.php
php artisan test --compact tests/Feature/DashboardTest.php --filter="awaiting reimbursement"
php artisan test --compact tests/Feature/Api/V1/Savings/SpendingApiTest.php
php artisan test --compact tests/Browser/SpendingReimbursementTest.php
php artisan test --compact
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`, logged-in user with locked income

1. Open **Spending** → **Add spending**
2. Enter amount, check **Expecting payback**, select who will pay
3. Confirm fund remaining drops and row shows **Awaiting payback**
4. Click **Record payback** → partial amount → **Partially repaid** badge
5. Record remaining payback → **Paid back** badge; remaining balance restored
6. Dashboard **Needs attention** shows awaiting payback when applicable

## Suggested commit

```
Summary: Add expecting-payback tracking on fund spends

Users can flag spends where someone will repay them, record partial or full
paybacks back to the fund, and see awaiting amounts on Spending and Dashboard.
```
