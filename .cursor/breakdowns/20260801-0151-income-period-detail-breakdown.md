# Income period detail page

**Date:** 2026-08-01

## Summary

Added a dedicated income period detail page with a plan breakdown table. Income index rows link to the detail page; lock/unlock remain on both pages.

## Changelog

- Click an income entry on **Savings → Income** to open its breakdown table (category, %, amount)
- Unlocked periods show a preview breakdown; locked periods show persisted allocations
- Index list no longer shows inline allocation bullets

## Files touched

**Backend**

- `app/Services/Savings/IncomeCalculationService.php` — `breakdownForPeriod()`
- `app/Http/Controllers/Savings/IncomePeriodController.php` — `show()`, slim index payload
- `routes/savings.php` — `savings.income.show`

**Frontend**

- `resources/js/pages/savings/income/show.tsx` (new)
- `resources/js/pages/savings/income/index.tsx` — clickable rows
- `resources/js/types/savings.ts` — `IncomeBreakdownRow`, `IncomePeriodSummary`

**Tests**

- `tests/Feature/Savings/IncomePeriodTest.php` (new)

## Deploy / verify

- `php artisan wayfinder:generate --no-interaction` if routes not auto-regenerated
- `npm run dev` for frontend

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

1. Log in, open **Savings → Income**
2. Save income, click the row → detail page with breakdown table
3. Lock from index or detail → status shows Locked
4. Lock/Unlock on index does not navigate away

## Suggested application commit

```
Summary: Add income period detail page with plan breakdown table

Users can click an income entry to view category allocations in a table. Unlocked periods show preview math; locked periods show saved allocations.
```
