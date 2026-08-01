---
name: Tier 1 — Improve Existing
overview: Close known gaps in the current savings loop — incomplete CRUD, flat reports, onboarding friction, and missing reminders. High impact, fits the existing envelope model without new domain entities (except optional notification prefs).
todos:
  - id: crud-gaps
    content: "Income period delete/edit, transfer update/cancel, demo savings seed"
    status: pending
  - id: team-vault
    content: "Implement TeamVault creation and DEK sharing for shared plans"
    status: pending
  - id: reports-dashboard
    content: "Date-range filters, spending list filters, simple charts, CSV export, days-until-broke"
    status: pending
  - id: onboarding
    content: "Video URLs in seeders, payday ritual wizard, contextual first-visit tips"
    status: pending
  - id: reminders
    content: "Unlocked income reminder, pending action reminder, optional payday reminder"
    status: pending
isProject: true
---

# Tier 1 — Improve What Exists

Parent: [20260801-2112-feature-roadmap-overview-plan.md](./20260801-2112-feature-roadmap-overview-plan.md)

## Problem

The core savings loop works but has rough edges: incomplete CRUD, table-only reports, commented-out demo seed, null guidance videos, and no proactive reminders. Survey pain points (forgetting transfers, no clear system) are addressable without new product positioning.

---

## 1.1 Complete CRUD and vault flows

### Income period delete / edit

| Area | Plan |
|------|------|
| **Database** | N/A if soft-delete not needed; hard delete cascades allocations/deductions |
| **Backend** | `destroy` + `update` on [`IncomePeriodController`](../../app/Http/Controllers/Savings/IncomePeriodController.php); block delete/edit when referenced spends exist or period is locked (or require unlock first) |
| **Services** | [`IncomeCalculationService`](../../app/Services/Savings/IncomeCalculationService.php) — recalc on amount edit when unlocked |
| **Frontend** | Edit/delete actions on [`income/show.tsx`](../../resources/js/pages/savings/income/show.tsx) and [`income/index.tsx`](../../resources/js/pages/savings/income/index.tsx) |
| **Tests** | Extend [`tests/Feature/Savings/IncomePeriodTest.php`](../../tests/Feature/Savings/IncomePeriodTest.php) |
| **Out of scope** | Edit locked period allocations (require unlock) |

### Transfer update / cancel

| Area | Plan |
|------|------|
| **Backend** | `update` + `destroy` on pending transfers in [`FundTransferController`](../../app/Http/Controllers/Savings/FundTransferController.php); confirmed transfers immutable |
| **Services** | [`FundTransferService`](../../app/Services/Savings/FundTransferService.php) |
| **Frontend** | Cancel/edit on [`transfers/index.tsx`](../../resources/js/pages/savings/transfers/index.tsx) |
| **Tests** | Extend transfer feature tests |

### Demo savings seed

| Area | Plan |
|------|------|
| **Seeders** | Uncomment/refactor demo block in [`DatabaseSeeder.php`](../../database/seeders/DatabaseSeeder.php); create user, team, plan from Abundant template, one locked income period, sample bank + recipient |
| **Factories** | Add missing [`IncomePeriodFactory`](../../database/factories/) |
| **Regression** | Login as demo user → dashboard checklist mostly complete |

### Team vault for shared plans

| Area | Plan |
|------|------|
| **Database** | [`team_vaults`](../../database/migrations/) table exists — verify columns |
| **Models** | [`TeamVault`](../../app/Models/TeamVault.php) + team relationship |
| **Services** | [`VaultKeyManager::activeDekForTeam()`](../../app/Services/Vault/VaultKeyManager.php) — create TeamVault when plan is shared or on explicit team unlock |
| **Frontend** | Team vault unlock flow if DEK differs from user vault |
| **Tests** | Shared plan member can decrypt amounts after team vault setup |
| **Blocks** | Tier 4 shared family visibility |

---

## 1.2 Reports and dashboard upgrades

### Date range filter on reports

- Filter by income period dropdown or calendar month
- [`SavingsReportController`](../../app/Http/Controllers/Savings/SavingsReportController.php) accepts `period_id` or `from`/`to`
- [`FundBalanceService::reportTotals()`](../../app/Services/Savings/FundBalanceService.php) scopes spends/transfers to range

### Spending list filters

- [`spending/index.tsx`](../../resources/js/pages/savings/spending/index.tsx): filter by fund, recipient, date range, pending/confirmed
- Server-side query params or client filter on paginated list

### Simple charts

- Fund `%` used horizontal bars on reports page
- Optional: spend total per income period (line or bar)
- Use lightweight chart lib or CSS-only bars for v1

### CSV export

- New route `GET /savings/reports/export?format=csv`
- Rows: date, fund, amount, recipient, description, status
- Subscription gate: consider `reports` feature or new `export` feature (Tier 5)

### Dashboard "days until broke"

- [`DashboardSummaryService`](../../app/Services/Dashboard/DashboardSummaryService.php): Everyday fund remaining ÷ avg daily spend (last 14 days)
- Display on [`SummaryStatCards`](../../resources/js/components/dashboard/summary-stat-cards.tsx)

---

## 1.3 Onboarding and guidance polish

| Item | Files |
|------|-------|
| Video URLs in seeders | [`SavingsFormulaTemplateSeeder`](../../database/seeders/SavingsFormulaTemplateSeeder.php), [`SavingsPlanPageGuidanceSeeder`](../../database/seeders/SavingsPlanPageGuidanceSeeder.php) — admin provides URLs or placeholder YouTube embeds |
| Payday ritual wizard | Extend [`SetupChecklist`](../../resources/js/components/dashboard/setup-checklist.tsx) → multi-step modal or dedicated `/savings/onboarding` page |
| Contextual tips | First-visit tooltips on [`plan.tsx`](../../resources/js/pages/savings/plan.tsx) keyed by `formula_template_id` |

---

## 1.4 Notifications and reminders

| Reminder | Implementation sketch |
|----------|----------------------|
| Unlocked income 7+ days | Scheduled command in [`routes/console.php`](../../routes/console.php) + notification class |
| Pending spends/transfers | Digest email or in-app badge (extends [`PendingActionsPanel`](../../resources/js/components/dashboard/pending-actions-panel.tsx)) |
| Payday reminder | User preference: `payday_day_of_month` on team or user settings |

| Area | Plan |
|------|------|
| **Database** | Optional `user_notification_preferences` or columns on `users` |
| **Jobs** | Queued notification jobs |
| **Settings** | Toggle in [`settings/profile.tsx`](../../resources/js/pages/settings/profile.tsx) or new notifications section |

---

## Suggested test commands (manual)

```bash
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
php artisan test --compact tests/Feature/Savings/FundTransferTest.php
vendor/bin/pint --dirty
npm run build
```

## Visual QA

1. Log in as demo seeded user
2. **Income** → delete unlocked period; edit amount → breakdown updates
3. **Transfers** → cancel pending transfer
4. **Reports** → filter by period; export CSV
5. **Spending** → filter by fund and date
6. Dashboard → days-until-broke stat visible when Everyday fund has spend history

## Out of scope (Tier 1)

- Recurring bills (Tier 2)
- Survey personalization (Tier 3)
- Automated payment (Tier 5)
