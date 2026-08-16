# Dashboard fund stat cards — breakdown

**Date:** 2026-08-17

## Summary

Replaced Dashboard summary stat cards **Total remaining** and **In banks** with fund-bucket totals: the default (Everyday) fund's remaining balance and the combined remaining across all other fund buckets.

## Changelog

- **Everyday Fund** (or the plan's default bucket name) stat card shows that bucket's remaining balance with a "Daily expenses" description when the bucket is named Everyday Fund
- **Other funds** stat card shows the sum of remaining balances for all non-default fund buckets
- **Needs attention** card unchanged
- Removed `summary.totalRemaining` and `summary.totalInBanks` from dashboard/API props (replaced by `defaultFundName`, `defaultFundRemaining`, `otherFundsRemaining`)
- `bankBalances` still loaded on the dashboard for other flows; only the summary aggregation was removed

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Default bucket identification | Retain existing `isDefault` / `defaultCategoryId()` logic | Everyday Fund by name, else first by sort_order |
| Banks stat card | Remove | Banks page link removed from summary cards |

## Files touched

**Backend**
- `app/Services/Dashboard/DashboardSummaryService.php`

**Frontend**
- `resources/js/components/dashboard/summary-stat-cards.tsx`
- `resources/js/pages/dashboard.tsx`
- `resources/js/types/dashboard.ts`
- `packages/shared/src/types/dashboard.ts`

**Tests**
- `tests/Feature/DashboardTest.php`
- `tests/Feature/Api/V1/DashboardTest.php`
- `tests/Browser/DashboardFundCardsTest.php`

## Deploy / verify

- `npm run build` (or `npm run dev`) if frontend changed
- No migration

## Suggested tests

```bash
php artisan test --compact tests/Feature/DashboardTest.php
php artisan test --compact tests/Feature/Api/V1/DashboardTest.php
php artisan test --compact tests/Browser/DashboardFundCardsTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` or `npm run build`

### Happy path

1. Log in as a user with the Abundant Formula plan and locked income (₱50,000)
2. Open **Dashboard**
3. Confirm stat cards show **Everyday Fund** · **₱35,000.00** and **Other funds** · **₱15,000.00**
4. Confirm **Needs attention** card still renders

### Checks

- [ ] No console errors
- [ ] Light and dark mode
- [ ] Fund bucket grid below stat cards unchanged

## Suggested commit

```
Summary: Replace dashboard total/in-banks stats with fund bucket totals

Show the default (Everyday) fund remaining and a combined total for all other
fund buckets instead of total remaining and in-banks summary cards.
```
