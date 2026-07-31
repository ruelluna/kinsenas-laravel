# Category deduction types breakdown

**Date:** 2026-08-01

## Summary

Savings plan categories now support two allocation types: **percentage** (must total 100%) and **deduction** (fixed peso or % of income, taken from a percentage source category). The plan page supports add/remove rows, type toggles, and a live percentage total. Income breakdown shows deduction context on preview and locked periods.

## Changelog

- Percentage and deduction category types on savings plans
- Plan page: add/remove categories, type toggle, deduction fields, live % total footer
- Income breakdown shows deduction notes (e.g. "from Everyday Fund", "− 1000 deduction")
- Lock validates deductions against actual income; fails if source would go negative
- Preset templates remain percentage-only

## Files touched

**Database**
- `database/migrations/2026_07_31_180017_add_allocation_type_to_savings_categories_table.php`

**Backend**
- `app/Enums/CategoryAllocationType.php`, `app/Enums/DeductionMode.php`
- `app/Models/SavingsCategory.php`
- `app/Services/Savings/CategoryAllocationCalculator.php`
- `app/Services/Savings/SavingsPlanService.php`
- `app/Services/Savings/IncomeCalculationService.php`
- `app/Http/Requests/Savings/SaveSavingsPlanRequest.php`
- `app/Http/Controllers/Savings/SavingsPlanController.php`
- `database/factories/SavingsCategoryFactory.php`

**Frontend**
- `resources/js/pages/savings/plan.tsx`
- `resources/js/pages/savings/income/show.tsx`
- `resources/js/types/savings.ts`

**Tests**
- `tests/Feature/Savings/SavingsPlanTest.php`
- `tests/Feature/Savings/IncomePeriodTest.php`

## Deploy steps

```bash
php artisan migrate
npm run dev   # or npm run build if frontend changed
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend not built

### Happy path

1. Log in as a user with a savings plan (e.g. `test@example.com` / `password`)
2. Open **Savings Plan** in the sidebar
3. Click **Add category**, set name "College Fund", type **Deduction**, mode **Fixed ₱**, value `1000`, source **Everyday Fund**
4. Confirm percentage total footer shows 100% (deduction rows excluded)
5. Click **Save plan**
6. Open **Income** → create or open a period with ₱50,000
7. Confirm breakdown: Everyday Fund shows deduction note, College Fund shows amount and "from …"
8. **Lock** the period and confirm amounts persist

### Checks

- [ ] No console errors
- [ ] Percentage total warns when ≠ 100%
- [ ] Remove category works (min 1 row)
- [ ] Locked income disables plan edits
- [ ] Light/dark mode on plan and income pages

### Regression

- [ ] Template selection still creates percentage-only plan
- [ ] Existing percentage-only plans still save and lock

## Suggested application commit

```
Summary: Add percentage and deduction savings category types

Plans can mix percentage rows (totaling 100%) with deduction rows that move
fixed or income-percent amounts from a source category. Plan UI supports
inline add/remove; income lock validates deductions against actual income.
```
