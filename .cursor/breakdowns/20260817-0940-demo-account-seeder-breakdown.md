# Demo Account Seeder — breakdown

**Date:** 2026-08-17

## Summary

Added an idempotent `DemoAccountSeeder` that provisions `demo@kinsenas.test` with a **7 Buckets** savings plan, six months of locked income and allocations, realistic spending and transfers, linked BDO bank spaces, and refreshed finance activity scores. Wired into `DatabaseSeeder` so `migrate:fresh --seed` always creates the demo account.

## Changelog

- New demo login: `demo@kinsenas.test` / `password` (Demo Member)
- 7 Buckets plan (`trc-savings` template) with 2 BDO bank spaces assigned to categories
- 6 monthly income periods (Mar–Aug 2026) with locked allocations
- Confirmed spends across Everyday, Enjoyment, Utility, Tithe, Educational, and Emergency buckets
- Monthly Everyday → Savings transfers; cross-bank Everyday → Emergency in April and July (auto-confirmed)
- Finance activity tier set to Active/Activated after seeding

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Seed wiring | Include in DatabaseSeeder | Demo always available after fresh seed |
| History depth | 6 monthly incomes | Long-time user feel without slow seeds |
| Pending actions | Avoid | Cross-bank transfers confirmed immediately |
| Email | `demo@kinsenas.test` | Distinct from `simple-plan-demo@kinsenas.test` |

## Files touched

### Seeders

- `database/seeders/DemoAccountSeeder.php` (new)
- `database/seeders/Support/DemoAccountHistory.php` (new)
- `database/seeders/DatabaseSeeder.php` (wired DemoAccountSeeder; removed old commented TRC block)

### Tests

- `tests/Feature/Seeders/DemoAccountSeederTest.php` (new)

## Deploy / migration

No new migrations. After deploy, run `php artisan db:seed --class=DemoAccountSeeder` on existing databases, or `migrate:fresh --seed` locally.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Seeders/DemoAccountSeederTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

### Happy path

1. Log in as `demo@kinsenas.test` / `password`
2. Unlock vault with `password` if prompted
3. Open **Savings Plan** — confirm **7 Buckets** with 7 categories and bank assignments
4. Open **Income** — confirm 6 locked periods (March–August 2026)
5. Open **Funds** — confirm non-zero balances and spend history per bucket
6. Open **Transfers** — confirm monthly savings moves and emergency top-ups

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Fund health shows BDO Payroll and GoSave banks
- [ ] Income allocation breakdown shows 7 buckets per period

## Suggested application commit

```
Summary: Add 7 Buckets demo account seeder with six months of history

Seeds demo@kinsenas.test with locked income, spends, transfers, and bank
assignments so migrate:fresh --seed yields a realistic long-time member account.
```

## Linear paste block

```
Title: Add 7 Buckets demo account seeder

Description:
Demo login demo@kinsenas.test / password now ships with migrate:fresh --seed.
Includes 7 Buckets plan, 6 months income, spends, transfers, and finance activity scores.

Comment / instructions:
No migration. Existing DBs: php artisan db:seed --class=DemoAccountSeeder.
Visual QA: log in → Savings Plan → Income → Funds → Transfers.
Suggested: php artisan test --compact tests/Feature/Seeders/DemoAccountSeederTest.php
```
