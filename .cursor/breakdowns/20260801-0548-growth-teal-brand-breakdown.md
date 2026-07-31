# Growth Teal brand color scheme

**Date:** 2026-08-01

## Summary

Applied the Growth Teal professional palette across FutureSave: deep teal primary, cool-tinted neutrals, harmonized chart tokens, and new success/warning/info semantic colors. Replaced all hardcoded emerald, amber, blue, and Laravel welcome hex values with theme tokens. Restyled the welcome page and auth split panel to match the admin app.

## Changelog

- Primary brand color is now deep teal (buttons, links, sidebar logo badge, auth panel)
- Added `success`, `warning`, and `info` semantic tokens for fund balances, setup checklist, alerts, and attention cards
- Fund balance tone helper uses `text-success`, `text-warning`, `text-destructive` consistently
- Alert component supports `success`, `warning`, and `info` variants; team invitation alert uses `info`
- Welcome page replaced with a clean FutureSave landing (no Laravel red/cream hex)
- Auth split layout left panel uses `bg-primary` instead of zinc-900
- Inertia progress bar uses teal `#0D7377`

## Files touched

### Theme

- `resources/css/app.css`

### UI primitives

- `resources/js/components/ui/alert.tsx`

### Shared lib

- `resources/js/lib/fund-balance-tone.ts`

### Dashboard

- `resources/js/pages/dashboard.tsx`
- `resources/js/components/dashboard/setup-checklist.tsx`
- `resources/js/components/dashboard/summary-stat-cards.tsx`

### Savings

- `resources/js/pages/savings/plan.tsx`
- `resources/js/pages/savings/transfers/index.tsx`

### Auth / landing

- `resources/js/pages/welcome.tsx`
- `resources/js/layouts/auth/auth-split-layout.tsx`
- `resources/js/components/app-logo.tsx`
- `resources/js/components/team-invitation-alert.tsx`
- `resources/js/app.tsx`

## Deploy steps

```bash
npm run dev
# or for production assets:
npm run build
```

No migrations or backend changes.

## Suggested tests (run manually)

No new automated tests — CSS token and class renames only. Existing Feature tests should pass unchanged.

```bash
npm run build
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`)

### Happy path

1. Open `/` — FutureSave welcome with teal logo badge and **Get started** / **Log in** buttons
2. Open **Log in** — left panel is teal; primary submit button is teal
3. Log in → **Dashboard** — setup checklist completed steps show green (success token)
4. If recovery key banner visible — amber/warning styling
5. Open **Spending** or **Transfers** — fund balances: green (healthy), amber (70%+ used), red (90%+)
6. Toggle dark mode in settings — confirm teal primary, success, warning readable

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Welcome page has no Laravel red accents
- [ ] Sidebar logo badge is teal in light and dark mode
- [ ] Team invitation alert on login/register uses info (blue-teal) styling
- [ ] Light and dark mode both usable

### Regression

- [ ] Login/logout still works
- [ ] Primary buttons and links remain readable

## Suggested application commit

```
Summary: Apply Growth Teal brand color scheme across FutureSave

Replace neutral shadcn defaults with a deep teal primary, cool neutrals, and
success/warning/info semantic tokens. Restyle welcome and auth panels; remove
hardcoded emerald/amber/blue classes from dashboard and savings pages.
```

## Linear paste block

```
Title: Apply Growth Teal brand color scheme

Description:
FutureSave now uses a professional Growth Teal palette — deep teal primary,
semantic success/warning/info tokens, and a restyled welcome page aligned with
the admin app. Fund balance tones and dashboard alerts use shared tokens instead
of hardcoded Tailwind colors.

Comment / instructions:
Run npm run dev after pull. Visual QA: welcome page, login panel, dashboard
checklist, spending/transfers fund colors, dark mode toggle. No migrations.

Documentation:
N/A
```
