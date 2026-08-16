# Fund bucket detail page — breakdown

**Date:** 2026-08-17

## Summary

Added a dedicated fund bucket detail page at `/{team}/savings/funds/{category}` showing balances, starting-balance history, income allocations, transfers (in/out), and spending. Fund card titles on Dashboard, Plan, Transfers, and Spending link to this page. Quick actions reuse existing modals (Add Existing Fund, Spend, Transfer).

## Changelog

- New route `savings.funds.show` and Inertia page **Savings Plan → fund name**
- Summary stats: starting balance, allocated, transferred out, received, spent, remaining
- Histories: `FundAddedEntry`, `IncomeAllocation`, `FundTransfer`, `FundSpend` filtered per category
- Clickable fund titles on all fund balance cards (`data-test="fund-card-title-{id}"`)
- Quick actions on detail page when vault unlocked and plan allows draws/transfers

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Page actions | Include quick actions | Reuse Add Existing Fund, Spend, Transfer modals |
| Card navigation | Click fund name/title | `detailHref` on `FundCardHeader` |

## Files touched

### Backend

- `app/Services/Savings/FundCategoryDetailService.php` (new)
- `app/Http/Controllers/Savings/FundCategoryController.php` (new)
- `routes/savings.php`

### Frontend

- `resources/js/pages/savings/funds/show.tsx` (new)
- `resources/js/types/savings.ts`
- `resources/js/components/savings/fund-card-header.tsx`
- `resources/js/components/savings/fund-balance-grid.tsx`
- `resources/js/components/savings/fund-balances-section.tsx`
- `resources/js/pages/dashboard.tsx`
- `resources/js/pages/savings/plan.tsx`
- `resources/js/pages/savings/transfers/index.tsx`
- `resources/js/pages/savings/spending/index.tsx`

### Tests

- `tests/Feature/Savings/FundCategoryShowTest.php` (new)
- `tests/Browser/FundCategoryShowTest.php` (new)
- `tests/Feature/Savings/SavingsPlanRequiredTest.php`

## Deploy / verify

- No migrations
- `npm run build` (or `npm run dev`) after frontend changes
- Wayfinder regenerated via Vite build

## Suggested tests (run manually)

```bash
# Feature

php artisan test --compact tests/Feature/Savings/FundCategoryShowTest.php
php artisan test --compact tests/Feature/Savings/SavingsPlanRequiredTest.php

# Browser (requires Playwright + built assets)

npm run build
php artisan test --compact tests/Browser/FundCategoryShowTest.php
php artisan test --compact tests/Browser/DashboardFundCardsTest.php

# Full suite (CI)

php artisan test --compact

# Lint

vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` or `npm run build`

### Happy path

1. Log in with a team that has a savings plan and locked income
2. Open **Dashboard** → click **Everyday Fund · 70%** (or any fund title)
3. Confirm detail page shows remaining balance, summary row, and section headings
4. Use **Add Existing Fund**, **Spend from …**, **Transfer from …** (when subscribed to transfers)
5. Confirm income allocation row links to income period detail

### Checks

- [ ] No console errors
- [ ] Fund titles on Plan, Transfers, Spending also link to detail
- [ ] Mobile width (~375px): sections stack readably
- [ ] Dark mode OK

### Regression

- [ ] Dashboard still shows allocation % on fund cards
- [ ] Login/logout still works

## Suggested commit

```
Summary: Add fund bucket detail page with ledger history

Members can open a single fund bucket to view balances, income allocations,
starting-balance entries, transfers, and spending. Fund card titles link to
the new page from Dashboard, Plan, Transfers, and Spending.
```

## Linear paste block

```
Title: Add fund bucket detail page with ledger history

Description:
Dedicated page at /{team}/savings/funds/{category} for one fund bucket: name/allocation %, remaining and summary totals, starting-balance history, income allocations (linked to periods), transfer in/out history, and spending history. Quick actions reuse existing modals. Fund card titles link here from Dashboard, Plan, Transfers, and Spending.

Comment / instructions:
No migration. Run npm run build after deploy. Visual QA: Dashboard → click fund title → verify sections and quick actions. Suggested: php artisan test --compact tests/Feature/Savings/FundCategoryShowTest.php and browser tests above.
```
