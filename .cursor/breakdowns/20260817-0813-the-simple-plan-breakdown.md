# The Simple Plan formula — breakdown

**Date:** 2026-08-17

## Summary

Added **The Simple Plan** (80% Everyday Fund, 20% Savings) as a new system formula template, with dedicated seeders, template sort order for chooser/homepage ordering, and a demo plan seeder.

## Changelog

- New formula template **The Simple Plan** — slug `simple-plan`, description “No, not the pop-punk band.”
- Plan chooser and landing page order: **The Simple Plan → The Abundant Formula → 7 Buckets → Custom**
- `sort_order` column on `savings_formula_templates` controls display order
- Abundant template display name aligned to **The Abundant Formula**
- `SimplePlanSeeder` — template only; `SavingsPlanSeeder` — demo member plan for `simple-plan-demo@kinsenas.test`

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| Savings bucket name | **Savings** (not Savings Fund) | Matches Abundant and 7 Buckets naming |
| Demo plan seeder | Standalone `SavingsPlanSeeder` | Not wired into `DatabaseSeeder` by default — run explicitly for demo data |

## Files touched

**Database**
- `database/migrations/2026_08_17_001302_add_sort_order_to_savings_formula_templates.php`
- `database/seeders/SimplePlanSeeder.php` (new)
- `database/seeders/SavingsPlanSeeder.php` (new)
- `database/seeders/SavingsFormulaTemplateSeeder.php`

**Backend**
- `app/Models/SavingsFormulaTemplate.php`
- `app/Http/Controllers/Savings/SavingsPlanController.php`
- `app/Http/Controllers/Admin/AdminSavingsFormulaTemplateController.php`

**Frontend**
- `resources/js/components/marketing/landing-content.ts`
- `resources/js/components/marketing/landing-formula-section.tsx`
- `resources/js/components/savings/plan-template-picker.tsx`

**Tests**
- `tests/Feature/Savings/SimplePlanTemplateTest.php` (new)
- `tests/Feature/Savings/SavingsPlanSeederTest.php` (new)

## Deploy

```bash
php artisan migrate
php artisan db:seed --class=SavingsFormulaTemplateSeeder
# Optional demo member plan:
php artisan db:seed --class=SavingsPlanSeeder
```

## Suggested tests

```bash
php artisan test --compact tests/Feature/Savings/SimplePlanTemplateTest.php
php artisan test --compact tests/Feature/Savings/SavingsPlanSeederTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `php artisan migrate`, `npm run dev`

1. Landing **Choose your blueprint** — four cards: Simple Plan, Abundant, 7 Buckets, Custom (left to right on wide screens)
2. Log in → **Savings Plan** chooser — **The Simple Plan** is first
3. Select **The Simple Plan** — plan shows Everyday Fund 80%, Savings 20%
4. Optional: `php artisan db:seed --class=SavingsPlanSeeder` → log in as `simple-plan-demo@kinsenas.test` / `password`

## Suggested commit

```
Summary: Add The Simple Plan formula template and demo seeder

Introduce an 80/20 Everyday Fund and Savings split as the first option
in the plan chooser and landing page, with sort_order on formula templates.
```
