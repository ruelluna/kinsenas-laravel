# Banks-first onboarding — breakdown

**Date:** 2026-08-02

## Summary

New users are guided to add bank references before choosing a savings plan. Sidebar and dashboard setup order put Banks first; the Banks page explains that accounts are references only (user transfers still happen in real banking apps); the plan chooser soft-gates with a warning when the team has no banks. Driver.js product tour is planned next (not installed yet).

## Changelog

- Sidebar order: **Dashboard → Banks → Savings Plan → Income → …**
- Dashboard **Get started** step 1 is now **Add your banks** (then plan, income, lock, spending)
- Dashboard shows **Add bank** quick action even before a plan exists
- Banks page: clearer description, info alert, richer empty state
- Plan chooser: **Add your banks first** warning with link to Banks (still allows choosing a formula)
- Plan guidance seeder copy mentions banks-first (applies on fresh seed / new guidance row)
- `data-tour` anchors added for upcoming Driver.js tour (`nav-banks`, `add-bank`, `banks-intro`, etc.)
- Plan file: `.cursor/plans/20260802-0043-driverjs-onboarding-tour-plan.md`

## Files touched

**Backend:** `DashboardSummaryService.php`, `SavingsPlanPageGuidanceSeeder.php`

**Frontend:** `banks/index.tsx`, `app-sidebar.tsx`, `nav-main.tsx`, `navigation.ts`, `plan-guidance-panels.tsx`, `plan-template-picker.tsx`, `plan.tsx`, `dashboard.tsx`, `setup-checklist.tsx`, `category-bank-select.tsx`

**Tests:** `DashboardTest.php`, `SavingsPlanTest.php`

**Plans:** `20260802-0043-driverjs-onboarding-tour-plan.md`

## Deploy

No migrations. Frontend rebuild required.

```bash
npm run dev
# or
npm run build
vendor/bin/pint --dirty
```

**Existing environments:** `SavingsPlanPageGuidanceSeeder` uses `firstOrCreate` — it will **not** overwrite admin-edited guidance. Update chooser copy in **Admin → Savings plan guidance** if you want banks-first wording on an existing DB, or `migrate:fresh --seed` locally.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/DashboardTest.php
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
vendor/bin/pint --dirty
```

Browser test: not required for this pass; add when Driver.js tour ships.

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`)

### Happy path

1. Log in as a new team user (no banks, no plan)
2. Confirm sidebar: **Banks** appears before **Savings Plan**
3. Dashboard **Get started** — Step 1 = **Add your banks**; **Continue setup** goes to Banks
4. Open **Banks** — read info alert + empty state; add a bank
5. Open **Savings Plan** — warning gone; choose a formula; assign funds to banks

### Soft gate

1. New team with zero banks → **Savings Plan**
2. Confirm **Add your banks first** warning + **Go to Banks**
3. Confirm **Use this formula** still works without banks

### Checks

- [ ] No console errors
- [ ] Sidebar active state on Banks / Savings Plan
- [ ] Light and dark mode on Banks alert + empty state
- [ ] Mobile width (~375px): Banks heading + Add bank usable

### Regression

- [ ] Login/logout still works
- [ ] Existing plan editor bank assignment still works when banks exist

## Suggested application commit

```
Summary: Guide users to add banks before choosing a savings plan

Reorder sidebar and dashboard setup so bank references come first, clarify
that banks are tracking references only, and soft-gate the plan chooser when
no banks exist. Driver.js tour planned separately.
```

## Linear paste block

```
Title: Guide users to add banks before choosing a savings plan

Description:
- Sidebar and dashboard checklist put Banks before Savings Plan
- Banks page explains reference-only accounts (user transfers elsewhere)
- Plan chooser shows a soft warning when the team has no banks
- Driver.js onboarding tour planned (not shipped yet)

Comment / instructions:
No migrate. Run npm run build (or npm run dev). Visual QA: new team → Banks first → Plan warning without banks → assign after adding banks. Suggested: php artisan test --compact tests/Feature/DashboardTest.php tests/Feature/Savings/SavingsPlanTest.php. Update Admin savings plan guidance copy on existing DBs if needed.
```
