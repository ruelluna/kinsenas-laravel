# Mobile layout pass

**Date:** 2026-08-04

## Summary

Broad mobile layout improvements across member-facing pages: card-based alternatives to wide tables, tighter page spacing, horizontal settings nav, stacked pending-action rows, and CSS breakpoint splits (`md:hidden` / `hidden md:block`) instead of JS-only mobile detection for spending and transfers.

## Changelog

- **Reports** — fund health shows stacked metric cards on mobile; desktop keeps the table.
- **Income detail** — breakdown table replaced by cards on mobile with a total summary card.
- **Plan guidance** — edit-rules table becomes a stacked list on mobile.
- **Settings** — nav is a horizontal scroll strip on mobile; vertical sidebar on `lg+`.
- **Recipients** — header wraps; **Add recipient** moves to bottom nav center action on mobile.
- **Dashboard** — pending actions stack confirm buttons; stat values scale down slightly on small screens.
- **Spending / Transfers** — mobile and desktop views render via Tailwind breakpoints (no layout flash from `useIsMobile`).
- **Global** — page content uses tighter vertical gap/padding on mobile; heading descriptions use smaller type on narrow screens; breadcrumbs truncate on small screens.

## Files touched

### New

- `resources/js/components/mobile/mobile-metric-card.tsx`

### Layout / shared

- `resources/js/components/page-content.tsx`
- `resources/js/components/heading.tsx`
- `resources/js/components/breadcrumbs.tsx`
- `resources/js/layouts/settings/layout.tsx`
- `resources/js/components/dashboard/pending-actions-panel.tsx`
- `resources/js/components/dashboard/summary-stat-cards.tsx`
- `resources/js/components/savings/plan-guidance-panels.tsx`

### Pages

- `resources/js/pages/savings/reports.tsx`
- `resources/js/pages/savings/income/show.tsx`
- `resources/js/pages/savings/recipients/index.tsx`
- `resources/js/pages/savings/spending/index.tsx`
- `resources/js/pages/savings/transfers/index.tsx`
- `resources/js/pages/savings/banks/index.tsx`
- `resources/js/pages/dashboard.tsx`

## Deploy / verify

- `npm run dev` or `npm run build` — frontend changed
- Manual check at `http://financial-literacy.test` on ~375px width and standalone PWA if installed

## Suggested tests (run manually)

```bash
npm run types:check

php artisan test --compact tests/Feature/DashboardTest.php
php artisan test --compact tests/Feature/Savings/FundSpendTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`)

### Happy path

1. Log in as a member with an active savings plan.
2. Resize to ~375px (or use device toolbar).
3. Confirm **bottom nav** shows (no sidebar); center **+** on Dashboard, Income, Spending, Transfers, Banks, Recipients.
4. Open **More** → confirm account card and overflow links work.
5. Visit **Reports** → fund health as cards, not horizontal scroll table.
6. Open an **Income** period → breakdown as cards.
7. Open **Settings** → horizontal nav tabs scroll; content readable below.
8. **Dashboard** → pending actions: Confirm button full-width below text on mobile.

### Checks

- [ ] No horizontal page scroll on Reports, Income detail, Settings
- [ ] Breadcrumb current page truncates instead of pushing header off-screen
- [ ] Light and dark mode on Reports cards and Settings nav
- [ ] Desktop (`md+`) still shows tables and sidebar

### Regression

- [ ] Login/logout and team switch still work
- [ ] Add spending / transfer / recipient via bottom nav center action

## Suggested application commit

```
Summary: Improve mobile layouts with card views and responsive nav

Replace wide tables with stacked cards on small screens for reports,
income detail, and plan guidance. Settings uses horizontal scroll tabs
on mobile; spending/transfers use md breakpoints instead of JS detection.
```

## Linear paste block

```
Title: Improve mobile layouts with card views and responsive nav

Description:
Member UI no longer relies on desktop tables on narrow viewports. Reports and income breakdown use metric cards; settings nav scrolls horizontally; recipients register a bottom-nav add action. Page spacing and breadcrumbs are tightened for phones.

Comment / instructions:
Run npm run dev or npm run build after deploy. Visual QA at ~375px: Reports, Income detail, Settings, Dashboard pending actions, bottom nav center actions. Suggested: npm run types:check; php artisan test --compact tests/Feature/DashboardTest.php
```
