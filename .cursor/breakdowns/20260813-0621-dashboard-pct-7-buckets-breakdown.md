# Dashboard allocation % + 7 Buckets rename — breakdown

**Date:** 2026-08-13

## Summary

Dashboard fund bucket cards now show each bucket’s plan allocation percentage inline after the title (e.g. `Everyday Fund · 70%`). User-facing “TRC / Truly Rich Club” branding is renamed to **7 Buckets**, with seeder defaults, marketing copy, and a data migration that backfills existing template names, plan names, and savings-plan guidance text.

## Changelog

- Dashboard fund cards display `{bucket name} · {allocation %}` when `showAllocationPercent` is enabled
- `FundBalance` API/Inertia payload includes `allocationType`, `percentage`, `deductionMode`, and `deductionValue`
- TRC formula template display name → **7 Buckets** (slug `trc-savings` unchanged)
- Savings plan chooser intro and marketing landing copy updated
- Migration backfills legacy plan/template/guidance strings

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Existing plan names | Backfill | Migration renames `TRC — Truly Rich Club` → `7 Buckets` |
| Internal slug / GHL tags | Retain | `trc-savings`, `trc-plan-chosen` unchanged |
| Allocation % visibility | Dashboard only | Plan / Spending / Transfers cards unchanged |

## Files touched

**Backend:** `FundBalanceService.php`, seeders (`SavingsFormulaTemplateSeeder`, `SavingsPlanPageGuidanceSeeder`), migration `2026_08_12_222205_rename_trc_to_seven_buckets.php`

**Frontend:** `category-allocation-label.ts`, `fund-card-header.tsx`, `fund-balance-grid.tsx`, `fund-balances-section.tsx`, `dashboard.tsx`, `plan-template-picker.tsx`, `landing-content.ts`, shared/JS `FundBalance` types

**Tests:** `FundBalanceServiceTest`, `DashboardTest`, `Api/V1/DashboardTest`, `RenameTrcToSevenBucketsTest`, `Browser/DashboardFundCardsTest`

## Deploy

```bash
php artisan migrate
npm run build   # if frontend not rebuilt in CI
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/DashboardTest.php
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Feature/Savings/RenameTrcToSevenBucketsTest.php
php artisan test --compact tests/Browser/DashboardFundCardsTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run build` if frontend changed

### Happy path

1. Log in as a member with a **7 Buckets** (or Abundant) plan and locked income
2. Open **Dashboard**
3. Confirm section title shows plan name (e.g. **7 Buckets**)
4. Confirm each fund card title includes allocation % (e.g. `Everyday Fund · 50%` or `· 70%` for Abundant)
5. Open **Savings Plan** chooser (new user) — template card titled **7 Buckets**, no TRC / Truly Rich Club copy
6. Landing page formula section shows **7 Buckets**

### Checks

- [ ] No console errors
- [ ] Plan / Spending / Transfers fund cards do **not** show allocation % (dashboard-only)
- [ ] Mobile width (~375px): card titles readable

## Suggested commit

```
Summary: Show dashboard fund allocation % and rename TRC to 7 Buckets

Dashboard fund cards now append each bucket’s plan percentage after the title.
User-facing TRC / Truly Rich Club branding becomes 7 Buckets with a data
migration for existing plans and guidance. Internal slug and GHL tags unchanged.
```
