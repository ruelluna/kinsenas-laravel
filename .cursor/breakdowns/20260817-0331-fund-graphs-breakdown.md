# Fund graphs — Dashboard + Reports

**Date:** 2026-08-17

## Summary

Added Recharts-based fund visualizations on the Dashboard (summary charts for all plan holders with spending) and Reports page (full chart suite behind the existing `reports` subscription feature). A new `FundGraphService` aggregates decrypted spending and income data server-side.

## Changelog

- Dashboard shows fund utilization bars and a 3-month spending trend when the team has confirmed spending
- Reports page adds date-range filter (`from` / `to`, default last 6 months) and five charts: utilization, spending trend, payday in vs out, spending by fund, top recipients
- Existing report tables retained as drill-down detail below charts
- API v1 `GET /savings/reports` returns `graphs` alongside `data`; optional `from` / `to` query params
- Added `recharts` dependency and shared chart components using `formatMoney` and allocation CSS tokens

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| Chart library | Recharts | Time-series and grouped bars; built frontend assets include chart bundle |
| Placement | Dashboard + Reports | Dashboard subset; full charts on Reports |
| Default range | Last 6 calendar months | Overridable via query params / date inputs |
| Dashboard gating | Utilization + trend for all plan users with spending | Full Reports charts remain behind `reports` feature |
| Income vs spending | Per income period boundaries | Aligns with payday-native model |

## Files touched

**Backend**
- `app/Services/Savings/FundGraphService.php` (new)
- `app/Services/Dashboard/DashboardSummaryService.php`
- `app/Http/Controllers/Savings/SavingsReportController.php`
- `app/Http/Controllers/Api/V1/Savings/ReportController.php`

**Frontend**
- `resources/js/components/charts/*` (new)
- `resources/js/components/dashboard/dashboard-charts-section.tsx` (new)
- `resources/js/pages/dashboard.tsx`
- `resources/js/pages/savings/reports.tsx`
- `resources/js/types/savings.ts`, `resources/js/types/dashboard.ts`
- `package.json` (+ recharts)

**Shared / API client**
- `packages/shared/src/types/savings.ts`, `dashboard.ts`, `index.ts`
- `packages/api-client/src/client.ts`

**Tests**
- `tests/Feature/Savings/FundGraphServiceTest.php` (new)
- `tests/Feature/Savings/SavingsReportTest.php` (new)
- `tests/Feature/DashboardTest.php`
- `tests/Feature/Api/V1/Savings/ReportApiTest.php`
- `tests/Browser/SavingsReportsChartsTest.php` (new)

## Deploy steps

- `npm run build` (or `npm run dev`) after deploy — new Recharts bundle on reports/dashboard pages
- No migrations

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundGraphServiceTest.php
php artisan test --compact tests/Feature/Savings/SavingsReportTest.php
php artisan test --compact tests/Feature/DashboardTest.php --filter=graph
php artisan test --compact tests/Feature/Api/V1/Savings/ReportApiTest.php
php artisan test --compact tests/Feature/Savings/FundBalanceServiceTest.php
php artisan test --compact tests/Browser/SavingsReportsChartsTest.php

vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` or `npm run build`

### Dashboard

1. Log in as a user with a plan, locked income, and at least one confirmed spend
2. Open **Dashboard**
3. Confirm **Fund utilization** horizontal bars and **Recent spending** area chart appear above fund balance cards
4. Click **View full reports →**

### Reports

1. Open **Reports** in the savings sidebar
2. Confirm charts render above fund health tables
3. Set **From** / **To** dates and click **Apply** — page reloads with filtered trend data
4. Confirm no console errors (DevTools → Console)
5. Mobile (~375px): charts stack vertically and remain readable

### Checks

- [ ] Tooltips show `₱` formatted amounts
- [ ] Dark mode: chart axes and cards readable
- [ ] Empty states when no spending in range

## Suggested application commit

```
Summary: Add fund graphs to dashboard and reports

Introduce FundGraphService and Recharts visualizations for fund utilization,
spending trends, payday in vs out, spending by fund, and top recipients.
Dashboard shows a compact summary; Reports adds date-range filtering and full charts.
```

## Linear paste block

```
Title: Add fund graphs to dashboard and reports

Description:
Dashboard shows fund utilization and a 3-month spending trend when spending exists.
Reports adds Recharts visualizations with date-range filtering plus existing tables.
API reports endpoint returns a graphs payload alongside totals.

Comment / instructions:
Run npm run build after deploy. Visual QA: Dashboard charts → Reports with date filter.
Suggested: php artisan test --compact tests/Feature/Savings/FundGraphServiceTest.php tests/Browser/SavingsReportsChartsTest.php
```
