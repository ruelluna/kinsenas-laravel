# Member dashboard content — breakdown

**Date:** 2026-08-01

## Summary

Replaced the placeholder member dashboard with a balanced home screen: setup checklist, summary stat cards, fund snapshot, pending confirmations, recent activity, and quick actions. Backend aggregates reuse `FundBalanceService` and savings list patterns via new `DashboardSummaryService`.

## Changelog

- Dashboard shows a **Get started** checklist (plan → income → lock → bank → spending) that collapses to **All set** when complete
- **Total remaining**, **In banks**, and **Needs attention** stat cards appear after locked income
- Fund balance grid with `% used` badges links to Savings Plan, Income, and Reports (when subscribed)
- **Pending actions** panel lists unconfirmed bank-attached spends and cross-bank transfers with Confirm buttons
- **Recent activity** feed merges last 8 confirmed spends and transfers
- Quick action buttons: Add income, Add spending, Transfer funds, Reports-gated transfers, Add bank
- Shared `FundBalanceGrid` extracted for dashboard, spending page, and savings plan page

## Files touched

### Backend

- `app/Services/Dashboard/DashboardSummaryService.php` (new)
- `app/Http/Controllers/DashboardController.php`

### Frontend

- `resources/js/pages/dashboard.tsx`
- `resources/js/types/dashboard.ts` (new)
- `resources/js/types/index.ts`
- `resources/js/lib/fund-balance-tone.ts` (new)
- `resources/js/components/dashboard/setup-checklist.tsx` (new)
- `resources/js/components/dashboard/summary-stat-cards.tsx` (new)
- `resources/js/components/dashboard/pending-actions-panel.tsx` (new)
- `resources/js/components/dashboard/recent-activity-feed.tsx` (new)
- `resources/js/components/savings/fund-balance-grid.tsx` (new)
- `resources/js/pages/savings/spending/index.tsx`
- `resources/js/pages/savings/plan.tsx`

### Tests

- `tests/Feature/DashboardTest.php`

## Deploy / verify

- No migrations
- `npm run dev` or `npm run build` for frontend changes

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/DashboardTest.php
vendor/bin/pint --dirty
npm run dev
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend changed

### Happy path

1. Log in as a user with locked income and recorded spending
2. Open **Dashboard** in the sidebar
3. Confirm setup checklist, stat cards, fund grid, pending actions, and recent activity render
4. Click **Record spending** and confirm navigation to Spending

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] New team without plan shows checklist hero only (no fund cards)
- [ ] Lock income message appears when plan exists but income not locked
- [ ] Pending bank spend shows in **Needs attention** and **Pending actions**
- [ ] Light and dark mode
- [ ] Mobile width (~375px): cards stack cleanly

### Regression

- [ ] Pending invitations modal still opens when invitations exist
- [ ] Spending and Savings Plan pages still show fund balance cards

## Suggested application commit

```
Summary: Add member dashboard summary, setup checklist, and activity panels

Replace placeholder dashboard cards with fund/bank totals, onboarding steps,
pending confirmations, and recent activity using DashboardSummaryService.
```

## Implementation summary

## Member dashboard content

- Setup checklist guides new teams through plan, income, lock, bank, and spending steps
- Summary cards show total remaining, bank totals, and attention count after locked income
- Fund snapshot grid with quick links to Savings Plan, Income, and Reports
- Pending actions and recent activity panels with confirm flows
- Quick action bar for common savings tasks
- Visual QA: see breakdown Visual QA section
