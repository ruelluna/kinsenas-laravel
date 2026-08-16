# Rename Emancipation Fund to Savings — breakdown

**Date:** 2026-08-17

## Summary

Renamed the 7 Buckets (`trc-savings`) fund bucket **Emancipation Fund** to **Savings** for new installs and backfilled existing template categories, member plan categories, and `fund_added_entries` snapshots via a data migration.

## Changelog

- 7 Buckets template 20% bucket is now **Savings** (was Emancipation Fund)
- Existing member plans with Emancipation Fund are backfilled to **Savings** on migrate
- Template `best_for` copy uses “(Savings)” instead of “(Emancipation)”
- `fund_added_entries.category_name` snapshots updated for dashboard/history consistency

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| Target bucket name | **Savings** | Same label as Abundant formula but different plans |
| TRC plan name backfill | Defer | Out of scope for this pass |
| Activity log history | Retain | Old audit JSON may still say Emancipation Fund |

## Files touched

**Database**

- `database/seeders/SavingsFormulaTemplateSeeder.php`
- `database/migrations/2026_08_17_030100_rename_emancipation_fund_to_savings.php`

**Tests**

- `tests/Feature/Savings/RenameEmancipationFundTest.php` (new)
- `tests/Feature/Savings/SavingsPlanTest.php`

## Deploy steps

1. `php artisan migrate`

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/RenameEmancipationFundTest.php
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `php artisan migrate`

### Happy path

1. Log in as a user with an existing 7 Buckets plan (previously showed Emancipation Fund)
2. Open **Dashboard** — confirm fund card shows **Savings · 20%** (not Emancipation Fund)
3. Open **Savings Plan** — confirm bucket row is **Savings**
4. Open **Income** — confirm breakdown column/header uses **Savings**

### Checks

- [ ] New user choosing **7 Buckets** gets **Savings** at 20% on plan setup
- [ ] Abundant formula **Savings** bucket unchanged (still 20% on 3-bucket plan)

## Suggested application commit

```
Summary: Rename Emancipation Fund to Savings in 7 Buckets plans

Backfill template categories, member plan categories, and fund-added
entry snapshots so existing users see Savings instead of Emancipation Fund.
```

## Linear paste block

```
Title: Rename Emancipation Fund to Savings in 7 Buckets

Description:
The 7 Buckets formula now labels its 20% long-term bucket as Savings instead
of Emancipation Fund. A data migration backfills existing template rows, member
plan categories, and fund_added_entries snapshots.

Comment / instructions:
Run php artisan migrate after deploy. Visual QA: existing 7 Buckets plan →
Dashboard and Savings Plan should show Savings at 20%. Suggested:
php artisan test --compact tests/Feature/Savings/RenameEmancipationFundTest.php
```
