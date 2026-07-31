# Lock percentages, allow custom category growth — breakdown

**Date:** 2026-08-01

## Summary

After the first income period exists, percentage categories on the savings plan are frozen. Users can still append new custom (deduction) categories over time. Save uses incremental merge instead of delete-all-recreate when income exists.

## Changelog

- Percentages lock after any income period (locked or preview)
- Existing categories cannot be edited or removed after income
- New custom categories can be appended from the plan page
- Share-with-team toggle remains editable
- Plan page shows locked state on existing rows; **Add custom category** when percentages are locked

## Files touched

**Backend**
- `app/Models/SavingsPlan.php` — `hasIncomePeriod()`
- `app/Services/Savings/SavingsPlanService.php` — merge save path
- `app/Http/Requests/Savings/SaveSavingsPlanRequest.php` — optional `categories.*.id`
- `app/Http/Controllers/Savings/SavingsPlanController.php` — `hasIncome`, `percentagesLocked` props

**Frontend**
- `resources/js/types/savings.ts`
- `resources/js/pages/savings/plan.tsx`

**Tests**
- `tests/Feature/Savings/SavingsPlanTest.php`

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
vendor/bin/pint --dirty
npm run dev
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

1. Create plan from template (no income yet) — full edit still works
2. Add income period (unlocked preview)
3. Return to **Savings Plan** — percentage rows show **Locked**, no Remove on existing rows
4. Click **Add custom category**, fill name + source, **Save plan**
5. Try changing a percentage — fields disabled; server rejects if tampered
6. Toggle **Share plan with team members** and save — should succeed

## Suggested application commit

```
Summary: Lock plan percentages after first income; allow custom category growth

Percentage categories become immutable once any income period exists. Custom
categories can still be appended via incremental merge save without breaking
allocation FKs. Share-with-team remains editable.
```
