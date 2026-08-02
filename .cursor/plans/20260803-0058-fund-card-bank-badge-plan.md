---
name: Fund card bank badge
overview: Extend FundBalance with bank metadata from category assignments, show bank icon + label on all fund surfaces (cards, tables, Dashboard/Plan sections), refactor Transfers to use FundBalanceGrid, and DRY the shared Fund balances section shell.
todos:
  - id: backend-fund-balance-bank
    content: Add bankId, bankDisplayName, bankLogoUrl to FundBalanceService::balancesForPlan() + fund_health report mapping
    status: pending
  - id: ts-fund-balance-type
    content: Extend FundBalance and ReportTotals fund_health types in resources/js/types/savings.ts
    status: pending
  - id: ui-fund-bank-badge
    content: Create FundBankBadge component (corner + inline table layouts) and FundCardHeader helper
    status: pending
  - id: ui-fund-balance-grid
    content: Extend FundBalanceGrid with bank badge, showReceived, and action props; use in Transfers
    status: pending
  - id: ui-fund-balances-section
    content: Extract FundBalancesSection wrapper shared by Dashboard and Savings Plan
    status: pending
  - id: ui-table-bank-badge
    content: Add bank badge to income breakdown and reports fund_health table rows
    status: pending
  - id: tests-bank-on-fund-balance
    content: Add FundBalanceService, report, and Inertia feature test assertions for bank fields
    status: pending
isProject: false
---

# Fund card bank badge

See the full plan content in the Cursor plan file `fund_card_bank_badge_a5ee5509.plan.md` — this repo copy tracks the same scope.

## Summary of scope (updated per user feedback)

1. **Backend** — `FundBalance` + reports `fund_health` include `bankId` / `bankDisplayName` / `bankLogoUrl` (snake_case in reports).
2. **Fund cards** — `FundBankBadge` + `FundCardHeader` in `FundBalanceGrid` (compact + detailed).
3. **Dashboard + Savings plan** — already use `FundBalanceGrid`; bank badges flow automatically. Extract shared `FundBalancesSection` for the duplicated section shell.
4. **Transfers** — refactor inline cards to `FundBalanceGrid` with `showReceived`, `transferredLabel`, and `action` props.
5. **Income breakdown + Reports fund health** — inline bank badge in Category/Fund table cells.
6. **Tests** — service, report, dashboard, and transfer page assertions.

## Out of scope

- Plan category edit form cards (bank already in `CategoryBankSelect`)
- Migrations
