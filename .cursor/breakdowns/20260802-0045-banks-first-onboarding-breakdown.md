# Banks-first onboarding — breakdown

**Date:** 2026-08-02

## Summary

New users are guided to add bank references before choosing a savings plan. Sidebar and dashboard setup order put Banks first; the Banks page explains that accounts are references only (user transfers still happen in real banking apps); the plan chooser soft-gates with a warning when the team has no banks. A Driver.js product tour auto-starts for incomplete setup and can be replayed from the Dashboard.

## Changelog

- Sidebar order: **Dashboard → Banks → Savings Plan → Income → …**
- Dashboard **Get started** step 1 is now **Add your banks** (then plan, income, lock, spending)
- Dashboard shows **Add bank** quick action even before a plan exists
- Banks page: clearer description, info alert, richer empty state
- Plan chooser: **Add your banks first** warning with link to Banks (still allows choosing a formula)
- Plan guidance seeder copy mentions banks-first (applies on fresh seed / new guidance row)
- `data-tour` anchors for Driver.js (`nav-banks`, `add-bank`, `banks-intro`, `setup-checklist`, `plan-main`, …)
- **Driver.js tour shipped:** multi-page walkthrough (Dashboard → Banks → Plan → Income), localStorage completion, **Take a tour** replay button
- Plan file: `.cursor/plans/20260802-0043-driverjs-onboarding-tour-plan.md`

### Changelog — 2026-08-02 (Driver.js)

- Added `driver.js` dependency + CSS theme
- `OnboardingTourHost` resumes tour across Inertia navigations
- Auto-start when setup incomplete; dismiss/complete persists per team in localStorage

## Files touched

**Backend:** `DashboardSummaryService.php`, `SavingsPlanPageGuidanceSeeder.php`

**Frontend:** `banks/index.tsx`, `app-sidebar.tsx`, `nav-main.tsx`, `navigation.ts`, `plan-guidance-panels.tsx`, `plan-template-picker.tsx`, `plan.tsx`, `dashboard.tsx`, `setup-checklist.tsx`, `category-bank-select.tsx`, `app.tsx`, `app.css`, `app-sidebar-layout.tsx`, `lib/onboarding-tour/*`, `components/onboarding/*`

**Deps:** `driver.js` in `package.json`

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
3. Dashboard **Get started** — Step 1 = **Add your banks**; tour should auto-start
4. Walk through tour: checklist → Banks → references → Add bank → Plan → Income
5. After Done/close, tour should not auto-restart; use **Take a tour** to replay
6. Open **Banks** — read info alert + empty state; add a bank
7. Open **Savings Plan** — warning gone; choose a formula; assign funds to banks

### Soft gate

1. New team with zero banks → **Savings Plan**
2. Confirm **Add your banks first** warning + **Go to Banks**
3. Confirm **Use this formula** still works without banks

### Checks

- [ ] No console errors
- [ ] Tour popover matches light/dark theme (teal primary, not purple)
- [ ] Sidebar active state on Banks / Savings Plan
- [ ] Light and dark mode on Banks alert + empty state
- [ ] Mobile width (~375px): Banks heading + Add bank usable; tour still readable

### Regression

- [ ] Login/logout still works
- [ ] Existing plan editor bank assignment still works when banks exist
- [ ] Closing tour mid-way does not loop on every dashboard visit

## Suggested application commit

```
Summary: Ship banks-first onboarding with Driver.js product tour

Guide new teams to add bank references before picking a plan, reorder
setup/nav accordingly, and walk users through Dashboard → Banks → Plan
with a themed Driver.js tour that persists completion per team.
```

## Linear paste block

```
Title: Ship banks-first onboarding with Driver.js product tour

Description:
- Sidebar and dashboard checklist put Banks before Savings Plan
- Banks page explains reference-only accounts (user transfers elsewhere)
- Plan chooser shows a soft warning when the team has no banks
- Driver.js tour auto-starts for incomplete setup; Take a tour to replay

Comment / instructions:
No migrate. Run npm install && npm run build (or npm run dev). Visual QA: new team → tour auto-start → Banks → Plan. Clear localStorage key kinsenas.onboardingTour.v1.* to retest auto-start. Suggested: php artisan test --compact tests/Feature/DashboardTest.php tests/Feature/Savings/SavingsPlanTest.php.
```
