# Rename Empower Fund to Utility — breakdown

**Date:** 2026-08-17

## Summary

Renamed the 7 Buckets (`trc-savings`) **Empower Fund** bucket to **Utility** for new installs and backfilled existing template categories, member plan categories, and `fund_added_entries` snapshots via a data migration.

## Changelog

- 7 Buckets template 5% bucket is now **Utility** (was Empower Fund)
- Existing member plans with Empower Fund are backfilled to **Utility** on migrate
- Landing page formula card and dashboard hint text updated
- Seeder description: repairs, maintenance, and household utilities

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| New name | **Utility** (not Utility Fund) | Matches user request; aligns with Tithe, Educational, Enjoyment naming |
| Activity log history | Retain | Old audit JSON may still say Empower Fund |

## Files touched

**Database**
- `database/migrations/2026_08_17_000631_rename_empower_fund_to_utility.php`
- `database/seeders/SavingsFormulaTemplateSeeder.php`

**Backend**
- `app/Services/Savings/FundBalanceService.php`

**Frontend**
- `resources/js/components/marketing/landing-content.ts`

**Tests**
- `tests/Feature/Savings/RenameEmpowerFundTest.php` (new)
- `tests/Feature/Savings/FundBalanceServiceTest.php`
- `tests/Feature/Savings/FundCategoryShowTest.php`

## Deploy

```bash
php artisan migrate
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/RenameEmpowerFundTest.php
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Feature/Savings/FundCategoryShowTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `php artisan migrate`, `npm run dev` if landing page changed

1. Log in as a user with an existing 7 Buckets plan (previously showed Empower Fund)
2. Open **Dashboard** — confirm fund card shows **Utility · 5%**
3. Open **Savings Plan** — category list shows **Utility**
4. Landing page **Choose your blueprint** — 7 Buckets card lists **Utility — 5%**

## Suggested commit

```
Summary: Rename Empower Fund to Utility in 7 Buckets plans

Backfill template categories, member plan categories, and fund-added
entry snapshots so existing users see Utility instead of Empower Fund.
```
