---
name: Feature Roadmap Overview
overview: Master index for Kinsenas feature tiers. Each tier is a separate plan in this folder — polish existing flows (Tier 1), deepen the envelope model (Tier 2), financial literacy (Tier 3), team/family (Tier 4), platform/monetization (Tier 5).
todos:
  - id: tier-1
    content: "Tier 1 — Improve existing (CRUD gaps, reports, onboarding, reminders)"
    status: pending
  - id: tier-2
    content: "Tier 2 — Envelope model extensions (bills, debt, goals, rollover, tags)"
    status: pending
  - id: tier-3
    content: "Tier 3 — Financial literacy & personalization (survey→plan, micro-lessons, i18n)"
    status: pending
  - id: tier-4
    content: "Tier 4 — Team and family features (shared vault, approvals, multi-income)"
    status: pending
  - id: tier-5
    content: "Tier 5 — Platform and monetization (pricing UX, tiers, PWA, receipts)"
    status: pending
isProject: true
---

# Kinsenas Feature Roadmap — Overview

Kinsenas is a **payday allocation / envelope-fund planner** ("Sweldo with a plan"), not a generic expense tracker. New work should deepen allocation literacy, not replace the fund-based model with a transaction ledger.

## Core loop (today)

```mermaid
flowchart LR
    Plan[SavingsPlan + formula] --> Income[IncomePeriod]
    Income -->|lock| Alloc[IncomeAllocation]
    Alloc --> Balance[Fund balances]
    Balance --> Spend[FundSpend]
    Balance --> Transfer[FundTransfer]
    Spend --> Reports[Reports + Dashboard]
```

## Tier plans

| Tier | Plan file | Theme | Suggested phase |
|------|-----------|-------|-----------------|
| 1 | [20260801-2112-tier-1-improve-existing-plan.md](./20260801-2112-tier-1-improve-existing-plan.md) | Polish core loop | Phase A (1–2 sprints) |
| 2 | [20260801-2112-tier-2-envelope-model-plan.md](./20260801-2112-tier-2-envelope-model-plan.md) | Bills, debt, goals, rollover | Phase B (2–4 sprints) |
| 3 | [20260801-2112-tier-3-financial-literacy-plan.md](./20260801-2112-tier-3-financial-literacy-plan.md) | Survey→plan, lessons, i18n | Phase C (strategic) |
| 4 | [20260801-2112-tier-4-team-family-plan.md](./20260801-2112-tier-4-team-family-plan.md) | Shared payday, family flows | Phase C (depends on Tier 1 team vault) |
| 5 | [20260801-2112-tier-5-platform-monetization-plan.md](./20260801-2112-tier-5-platform-monetization-plan.md) | Pricing, tiers, PWA | Ongoing / parallel |

## Cross-tier dependencies

```mermaid
flowchart TB
    T1[Tier 1: Team vault + CRUD] --> T4[Tier 4: Shared family]
    T1 --> T2[Tier 2: Reports export]
    T3[Tier 3: Survey to plan] --> T1
    T2 --> T5[Tier 5: Feature gating]
    T1 --> T5
```

## What to avoid (all tiers)

- Full double-entry transaction ledger (Mint-style)
- Bank statement import / Open Banking (PH fragmented; defer)
- Merchant auto-categorization / OCR (defer)
- Multi-currency (PH-first is the moat)
- Monthly budgets that ignore payday rhythm

## Impact checklist (every tier implementation)

| Area | Ask |
|------|-----|
| Database | New tables/columns? FK changes? Data backfill? |
| Models & relationships | New models? Factory states? |
| Seeders & demo data | `migrate:fresh --seed` still usable? Order? Idempotent? |
| Enums & permissions | Roles, subscription features, policies? |
| Routes & Wayfinder | New routes → TS regen? |
| Form requests & validation | New/changed fields? Authorization? |
| Services & events | Business logic? Queued jobs? |
| Inertia props & TS types | Prop shape changes in `resources/js/types/`? |
| UI components | Which pages consume changed props? |
| Print / export / reports | Same data shown elsewhere? |
| Tests | Feature tests under `tests/Feature/Savings/` |

## Suggested execution order

**Phase A:** Tier 1 (except team vault if blocked)  
**Phase B:** Tier 2 items in order — recurring obligations → goal targets → rollover → spend tags → charts/export  
**Phase C:** Tier 1 team vault → Tier 4 → Tier 3 survey bridge → Tier 3 i18n  
**Parallel:** Tier 5 pricing UX when billing mode goes live

Pick a tier plan file to expand into an implementation breakdown when ready to ship.
