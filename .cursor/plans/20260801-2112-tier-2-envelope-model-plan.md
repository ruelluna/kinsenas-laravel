---
name: Tier 2 — Envelope Model Extensions
overview: New features that address survey pain points (bills, debt, irregular income, goals) while staying fund-centric — planned obligations, lightweight debt tracking, savings targets, rollover rules, multi-source income, and optional spend tags.
todos:
  - id: recurring-obligations
    content: "RecurringObligation entity or extended deductions — bills linked to fund + recipient + frequency"
    status: pending
  - id: debt-tracking
    content: "Debt payment fund with target balance, minimum payment, payoff report"
    status: pending
  - id: savings-goals
    content: "Optional target amount + date per SavingsCategory with progress UI"
    status: pending
  - id: multi-income
    content: "Multiple income lines per period with source tags and planned vs actual"
    status: pending
  - id: fund-rollover
    content: "Per-category rollover rules at income lock (reset, roll forward, sweep to savings)"
    status: pending
  - id: spend-tags
    content: "Optional spend_tag on FundSpend with drill-down report"
    status: pending
isProject: true
---

# Tier 2 — New Features That Fit the Envelope Model

Parent: [20260801-2112-feature-roadmap-overview-plan.md](./20260801-2112-feature-roadmap-overview-plan.md)

## Problem

Survey respondents prioritize bills, rent, tuition, debt, and family support — but Kinsenas only models **fund buckets** and one-off spends. Users with irregular income (freelancers, OFW) can't split sweldo sources. Unused Everyday balance disappears each payday with no rollover option.

**Principle:** Extend the envelope model; do not add a parallel expense category system.

---

## 2.1 Recurring obligations (bills as plan deductions)

### Target model

```
RecurringObligation
  savings_plan_id (FK)
  savings_category_id (FK) — fund to draw from
  recipient_id (FK, optional)
  name (e.g. "Meralco", "Rent")
  amount (encrypted)
  frequency: monthly | per_payday | yearly
  due_day_of_month (optional)
  is_active
```

On **income lock**, system pre-fills [`IncomePeriodDeduction`](../../app/Models/IncomePeriodDeduction.php) or suggests spends for matching obligations.

### Impact checklist

| Area | Detail |
|------|--------|
| **Database** | New `recurring_obligations` table; UUID PKs |
| **Models** | `RecurringObligation` + relationships to plan, category, recipient |
| **Factories/seeders** | Demo obligations in Tier 1 demo seed |
| **Services** | `RecurringObligationService` — sync to period deductions on lock |
| **Routes** | CRUD under `/{team}/savings/obligations` or section on plan page |
| **Frontend** | "Monthly bills" panel on [`plan.tsx`](../../resources/js/pages/savings/plan.tsx) or income show |
| **Subscription** | Gate under `savings_plan` or new feature |
| **Tests** | Lock income → obligations appear as deductions/suggestions |

### Out of scope

- Auto-pay / bank debit integration
- Full recurring transaction engine with cron-generated spends

---

## 2.2 Debt tracking (lightweight)

### Target model

Option A — extend category with debt metadata:

```
SavingsCategory (add columns)
  is_debt_fund: bool
  debt_target_balance (encrypted, optional)
  debt_minimum_payment (encrypted, optional)
```

Option B — separate `DebtAccount` linked to category.

Payments = [`FundSpend`](../../app/Models/FundSpend.php) to recipient type (extend [`RecipientType`](../../app/Enums/RecipientType.php) with `Debt` or use existing `Other`).

### Reports

- Remaining debt (target − sum of payments)
- Paid this income period
- Simple months-to-payoff (remaining ÷ avg payment) — no amortization v1

### Files

- [`FundBalanceService`](../../app/Services/Savings/FundBalanceService.php) — debt section in `reportTotals()`
- [`reports.tsx`](../../resources/js/pages/savings/reports.tsx) — debt card

---

## 2.3 Savings goals with targets

### Target model

```
SavingsCategory (add)
  goal_target_amount (encrypted, nullable)
  goal_target_date (date, nullable)
```

### UI

- Progress bar on [`FundBalanceGrid`](../../resources/js/components/savings/fund-balance-grid.tsx)
- Status: on track / behind / reached (allocated + remaining vs target)

### Logic

- "On track" = current balance ≥ linear interpolation from start to target date
- No separate Goal model v1 — keeps fund-centric

---

## 2.4 Irregular and multi-source income

### Target model

```
IncomePeriod (unchanged header)
IncomePeriodLine (new)
  income_period_id
  label (e.g. "Salary", "Remittance")
  source_tag: employee | freelance | remittance | business | other
  amount (encrypted)
  sort_order
```

Total period amount = sum of lines. Lock allocates on total.

### UI

- [`add-income-modal.tsx`](../../resources/js/components/savings/add-income-modal.tsx) — add/remove lines
- Income show: planned vs actual if variable mode enabled

### Migration

- Backfill existing periods as single line with amount from period

---

## 2.5 Fund rollover rules

### Target model

```
SavingsCategory (add)
  rollover_mode: reset | roll_forward | sweep_to_category
  sweep_to_category_id (FK, nullable)
```

### Lock-time behavior ([`IncomeCalculationService`](../../app/Services/Savings/IncomeCalculationService.php))

| Mode | Behavior |
|------|----------|
| `reset` | Current behavior — remaining ignored |
| `roll_forward` | Add previous period remaining to allocation base |
| `sweep_to_category` | Auto-create transfer suggestion from remaining to target fund |

### UI

- Toggle per category on [`plan.tsx`](../../resources/js/pages/savings/plan.tsx) edit mode

---

## 2.6 Optional spend tags

### Target model

```
FundSpend (add)
  spend_tag (string, nullable, max 50) — or enum SpendTag
```

Preset tags: groceries, transport, dining, utilities, other — user can type custom.

### Reports

- Drill-down: select fund → breakdown by tag
- Filter on spending index

### Out of scope

- Tags replacing fund categories
- Auto-tagging from description

---

## Implementation order (within Tier 2)

1. Spend tags (smallest schema change)
2. Savings goal targets
3. Recurring obligations
4. Fund rollover
5. Multi-source income
6. Debt tracking

## Suggested test commands (manual)

```bash
php artisan test --compact tests/Feature/Savings/
vendor/bin/pint --dirty
php artisan migrate
npm run build
```

## Dependencies

- Tier 1 demo seed helps QA all Tier 2 flows
- Tier 1 CSV export useful for obligation/debt reports

## Out of scope (Tier 2)

- Bank import
- OCR receipts
- Full loan amortization calculator
