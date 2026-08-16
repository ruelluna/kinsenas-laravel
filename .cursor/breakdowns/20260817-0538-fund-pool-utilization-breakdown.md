# Fund pool utilization fix

**Date:** 2026-08-17

## Summary

Fund "% used" (utilization) now measures consumption against the full spendable pool—starting balance + allocated income + received transfers − transferred out—instead of allocated income alone. This prevents spending from existing money from showing >100% and aligns the badge with Remaining.

## Changelog

- Fund `% used` = effective spent ÷ total spendable pool × 100, clamped 0–100
- Opening balance and "Add existing fund" money included in utilization denominator
- Reimbursements reduce `% used` (uses net effective spent, same as Remaining)
- Transfers alone no longer inflate `% used` on the source bucket
- Buckets with only starting balance (no income yet) now show `% used`
- Fund utilization chart copy mentions existing savings, not only income

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Utilization semantics | Pool utilization | Denominator = full spendable pool; aligns with Remaining |
| Transfers in % used | Exclude from consumed | Only effective spending counts; transfers reposition money |
| Reimbursements | Include in consumed | Use `effectiveSpent` in numerator |

## Files touched

### Backend

- `app/Services/Savings/FundBalanceService.php` — new `percentUsed` formula

### Tests

- `tests/Feature/Savings/FundBalanceServiceTest.php` — five new utilization cases

### Frontend

- `resources/js/components/charts/fund-utilization-chart.tsx` — description / empty copy

## Deploy / migration

No migration. No Wayfinder regen.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Feature/Savings/FundGraphServiceTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`) if frontend changed

### Happy path

1. Log in as a user with a savings plan
2. Add existing fund to Everyday Fund, then record income
3. Spend across opening + allocated amounts
4. Confirm fund card **"% used"** stays ≤100% and matches intuition vs Remaining
5. Open **Reports** → fund health table — same percentages
6. Reimburse a spend — confirm `% used` drops

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Fund utilization chart empty message mentions existing savings
- [ ] Light and dark mode on dashboard fund cards

## Suggested application commit

```
Summary: Fix fund utilization to use full spendable pool

Fund "% used" now divides effective spent by the total spendable pool
(starting balance + allocated + net transfers), keeping utilization at
0–100% and aligned with Remaining. Reimbursements and opening balances
are accounted for; transfers alone no longer inflate utilization.
```

## Linear paste block

```
Title: Fix fund utilization to use full spendable pool

Description:
Fund "% used" now measures consumption against the full spendable pool
(starting balance + allocated income + received − transferred out), not
allocated income alone. Fixes >100% when spending from existing money;
reimbursements reduce utilization; transfer-only activity no longer
inflates the metric.

Comment / instructions:
No migration. Visual QA: add existing fund + income, spend, confirm
"% used" ≤100% on dashboard and Reports. Suggested:
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Feature/Savings/FundGraphServiceTest.php
```
