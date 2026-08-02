# Alert banner variants — breakdown

**Date:** 2026-08-03

## Summary

Standardized permanent alert banner colors with two new `Alert` variants (`guidance`, `brand`) and a shared `PublicBetaAlert` component. Replaced ad-hoc Tailwind overrides and inconsistent `default` / `info` usage across beta and onboarding banners.

## Changelog (user-visible)

- Neutral onboarding tips (e.g. “Already saving?”, trial signup) use a muted **guidance** style — light grey background, subtle border.
- Public beta messaging uses a consistent **brand** style — primary-tinted border and title, shared copy via `PublicBetaAlert`.
- Caution banners on the savings plan page use the full **warning** variant (no manual icon color classes).
- Top-of-app beta strip and **Settings → Billing** beta banner now match visually.

## Files touched

**Design system**

- `resources/js/components/ui/alert.tsx` — `guidance` and `brand` variants
- `resources/js/components/public-beta-alert.tsx` — new shared beta banner

**Consumers**

- `resources/js/components/open-beta-banner.tsx`
- `resources/js/pages/settings/billing.tsx`
- `resources/js/pages/auth/register.tsx`
- `resources/js/pages/savings/plan.tsx`
- `resources/js/components/savings/plan-guidance-panels.tsx`

## Deploy / verify

- No migrations.
- `npm run dev` or `npm run build` if frontend not already running.

## Suggested tests (run manually)

```bash
npm run types:check
npm run lint:check
```

No PHP tests required (frontend-only).

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Happy path

1. Log in as an approved beta user.
2. Confirm the top **Public beta — free access** strip uses primary-tinted title and matches **Settings → Billing** beta alert.
3. Open **Savings → Plan** (no income yet) — **Already saving?** banner is muted grey (guidance).
4. With income recorded — **Custom fund bucket changes…** banner is amber/warning styled.
5. Log out → **Register** with beta offer — beta apply banner uses brand styling; trial offer uses guidance.

### Checks

- [ ] Light and dark mode on guidance, brand, and warning alerts
- [ ] No console errors
- [ ] Alert description text remains readable (muted body, colored title where intended)

## Suggested commit

```
Summary: Standardize permanent alert banner variants (guidance, brand)

Add guidance and brand Alert variants plus PublicBetaAlert so beta and
onboarding banners share consistent colors instead of one-off class overrides.
```

## Implementation summary

- **guidance** — neutral permanent tips (optional steps, trial signup).
- **brand** — product/beta messaging with primary accent.
- **info / warning / destructive / success** — unchanged semantics for how-to, action needed, errors, confirmation.
- **PublicBetaAlert** — single source for beta title + `BETA_FREE_MESSAGE`; pages pass extra paragraphs as children.

## Changelog (2026-08-03 — guidance visual refresh)

- **guidance** alerts use a primary teal left accent, tinted background, and colored icon (no longer flat grey).
- **brand** alerts slightly stronger tint + primary title for clear separation from guidance.
- Alert titles no longer clamp to one line (long headings wrap naturally).
- “Already saving?” uses an info icon instead of a warning triangle.
