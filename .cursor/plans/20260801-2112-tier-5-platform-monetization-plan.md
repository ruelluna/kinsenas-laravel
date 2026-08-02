---
name: Tier 5 — Platform and Monetization
overview: Adjacent product work — finalize pricing UX, differentiate subscription tiers, automated payments, mobile quick-add, and receipt gallery. Can run parallel to savings feature tiers.
todos:
  - id: pricing-ux
    content: "Replace 'pricing coming soon' on landing and register with real plan prices"
    status: pending
  - id: feature-tiers
    content: "Gate reports/export/debt/advanced features by SubscriptionFeature plan matrix"
    status: pending
  - id: automated-payment
    content: "Evaluate PayMaya/Stripe integration vs manual QR for live billing mode"
    status: pending
  - id: mobile-pwa
    content: "PWA manifest + dashboard FAB for quick Everyday fund spend"
    status: pending
  - id: receipt-gallery
    content: "Gallery view and search for fund spend receipt uploads"
    status: pending
isProject: true
---

# Tier 5 — Platform and Monetization

Parent: [20260801-2112-feature-roadmap-overview-plan.md](./20260801-2112-feature-roadmap-overview-plan.md)

## Problem

Open beta shows **"Pricing: coming soon"** on [`register.tsx`](../../resources/js/pages/auth/register.tsx) and [`landing-hero.tsx`](../../resources/js/components/marketing/landing-hero.tsx). All subscription features ([`SubscriptionFeature`](../../app/Enums/SubscriptionFeature.php): `savings_plan`, `transfers`, `reports`) ship on one Basic plan ([`BillingSeeder`](../../database/seeders/BillingSeeder.php)). Manual QR billing works for beta but won't scale at live launch.

---

## 5.1 Finalize pricing UX

### Current state

- [`BillingController`](../../app/Http/Controllers/Settings/BillingController.php) + [`BillingPlanPresenter`](../../app/Services/Billing/BillingPlanPresenter.php)
- [`formatMoneyFromCents`](../../resources/js/lib/format-money.ts) for prices
- [`BETA_PRICING_COMING_SOON_LABEL`](../../resources/js/lib/beta-copy.ts)

### Work

| Surface | Change |
|---------|--------|
| Landing hero | Show actual Basic price when `BillingMode::Live` |
| Register | Remove coming-soon; show trial terms |
| Billing settings | Plan comparison table if multiple plans |
| Open beta banner | Keep discount messaging; link to billing |

### Config

- [`config/billing.php`](../../config/billing.php) — `BILLING_MODE=live` switch
- Admin manages plans at `/admin/plans`

### Out of scope

- Multi-currency pricing

---

## 5.2 Feature-tier differentiation

### Proposed matrix (example)

| Feature | Basic | Pro |
|---------|-------|-----|
| Savings plan, income, banks | yes | yes |
| Transfers | yes | yes |
| Reports (basic) | yes | yes |
| CSV export | no | yes |
| Debt module (Tier 2) | no | yes |
| Recurring obligations | limited | unlimited |
| Team shared plan (Tier 4) | no | yes |

### Implementation

- Add plan features in [`SubscriptionPlan`](../../app/Models/SubscriptionPlan.php) pivot or JSON column
- Extend [`EnsureSubscriptionFeature`](../../app/Http/Middleware/EnsureSubscriptionFeature.php) middleware
- New enum cases in [`SubscriptionFeature`](../../app/Enums/SubscriptionFeature.php): `export`, `debt`, `shared_plan`, etc.
- Upgrade CTAs in gated UI ([`app-sidebar.tsx`](../../resources/js/components/app-sidebar.tsx) already collapses without subscription)

### Seeders

- Update [`BillingSeeder`](../../database/seeders/BillingSeeder.php) with Pro plan + feature flags

### Tests

- User on Basic blocked from export route; Pro allowed

---

## 5.3 Automated payment

### Current flow

1. User submits payment proof ([`PaymentSubmissionController`](../../app/Http/Controllers/Billing/PaymentSubmissionController.php))
2. Admin approves ([`AdminPaymentSubmissionController`](../../app/Http/Controllers/Admin/AdminPaymentSubmissionController.php))
3. Subscription activated

### Options for live mode

| Driver | Pros | Cons |
|--------|------|------|
| PayMaya / Maya Business | PH-native, config stub exists | Integration effort |
| Stripe | Well-documented | PH card coverage |
| Keep manual QR | Zero integration | Ops overhead |

### Plan

- Spike: payment webhook → auto-activate subscription
- Keep manual path as fallback
- Log all payment events for admin audit

---

## 5.4 Mobile PWA / quick-add spend

Detailed plan: [20260801-2115-pwa-mobile-exploration-plan.md](./20260801-2115-pwa-mobile-exploration-plan.md)

### PWA

- `manifest.json` + service worker via Vite plugin
- Install prompt on mobile browsers
- Icons and theme color matching brand
- Full mobile polish (sidebar, safe-area, key page layouts) before install prompt

### Quick-add FAB

- Floating button on [`dashboard.tsx`](../../resources/js/pages/dashboard.tsx) when setup complete
- Opens [`add-spending-modal.tsx`](../../resources/js/components/savings/add-spending-modal.tsx) pre-selected to Everyday fund
- Reduces friction for impulse logging (survey pain: impulse spending)

### Out of scope v1

- Native iOS/Android apps
- Offline spend queue (vault requires unlock)

---

## 5.5 Receipt gallery

### Current state

- [`FundSpend`](../../app/Models/FundSpend.php) has `receipt_image_path`
- Upload in add/edit spending modals

### Enhancements

| Feature | Detail |
|---------|--------|
| Gallery page | Grid of receipt thumbnails under `/savings/receipts` or tab on spending |
| Lightbox | Full-size view |
| Search | Filter by date, fund, description |
| Storage | Confirm disk config (`storage/app/...`) and team scoping |

### Privacy

- Receipts encrypted at rest? (evaluate — images may not use `UserEncryptedMoney`)
- Policy: only team members with plan access

---

## Impact checklist

| Area | Tier 5 touch |
|------|--------------|
| Database | Plan feature pivot; payment webhook log table |
| Admin | Plan feature editor; payment reconciliation |
| Frontend | Pricing copy, upgrade modals, PWA assets, receipt gallery |
| Middleware | New subscription feature gates |
| Docs | Admin billing guide when live (user-requested only) |

---

## Dependencies

- Tier 1 CSV export → gate as Pro feature
- Tier 2 debt module → gate as Pro feature
- Tier 4 shared plan → gate as Pro feature

---

## Suggested test commands (manual)

```bash
php artisan test --compact tests/Feature/Billing/
php artisan test --compact tests/Feature/OpenBeta/
npm run build
```

## Visual QA

1. Set `BILLING_MODE=live` → landing shows price
2. Basic user → export blocked with upgrade CTA
3. Mobile width → PWA install banner; FAB visible on dashboard
4. Spending → receipt gallery shows uploaded images

## Out of scope (Tier 5)

- Affiliate/referral program
- Usage-based billing
- Enterprise SSO
