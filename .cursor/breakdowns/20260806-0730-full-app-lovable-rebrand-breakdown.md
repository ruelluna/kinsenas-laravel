# Full-app Lovable rebrand

**Date:** 2026-08-06

## Summary

Replaced Kinsenas global design tokens with the Lovable palette (midnight, surface, glow, gold, teal, clay, lilac), loaded DM Sans + Space Grotesk app-wide, synced mail/PWA/mobile brand colors, and rebuilt the welcome page to match [sweldo-plan-vault.lovable.app](https://sweldo-plan-vault.lovable.app/) section order and copy. Settings → Appearance (system / light / dark) is unchanged.

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Theme default | Keep `use-appearance` | Light and dark Lovable token sets both live in `app.css` |
| Welcome page | Lovable-exact | New Filipino spending + banks sections; removed sticky open-beta banner |
| Allocation slots | Keep `allocation-1…7` names | Recolored to Lovable bucket hues |

## Changelog

- Global CSS: Lovable OKLCH tokens, `--radius: 0.75rem`, `font-dm` / `font-space` utilities
- Fonts: Instrument Sans → DM Sans (body) + Space Grotesk (headings / money in key surfaces)
- Welcome: hero demo card, nav anchors, theme toggle, comparison cards, banks grid, loop + app preview, security block, beta footer
- Brand: mail `#1E8B75`, PWA theme/background, Tamagui mobile tokens, Inertia progress bar
- Removed welcome usage of open-beta sticky banner, trust strip, outcome section, parallax hero

## Files touched

**CSS / config:** `resources/css/app.css`, `vite.config.ts`, `config/brand.php`, `resources/views/app.blade.php`, `resources/views/vendor/mail/html/themes/default.css`, `packages/ui/src/theme.ts`, `resources/js/app.tsx`, `resources/js/lib/brand.ts`, `public/brand/app-preview.jpg`

**Marketing:** `landing-content.ts`, `landing-header.tsx`, `landing-hero.tsx`, `landing-hero-demo-card.tsx`, `landing-theme-toggle.tsx`, `landing-emotional-problem.tsx`, `landing-filipino-spending.tsx`, `landing-banks.tsx`, `landing-how-it-works.tsx`, `landing-formula-section.tsx`, `landing-privacy.tsx`, `landing-final-cta.tsx`, `welcome.tsx`

**Typography pass:** `heading.tsx`, `summary-stat-cards.tsx`, `fund-balance-grid.tsx`, `mobile-metric-card.tsx`

**Tests:** `WelcomePageTest.php`, `WelcomeTest.php`, `BrandedMailTemplateTest.php`

## Deploy / verify

- `npm run dev` or `npm run build` (fonts + PWA manifest)
- No migrations

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/WelcomePageTest.php
php artisan test --compact tests/Browser/WelcomeTest.php
php artisan test --compact tests/Feature/PwaManifestTest.php
php artisan test --compact tests/Feature/Mail/BrandedMailTemplateTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` or `npm run build`

### Welcome (guest)

1. Open `/` — dark midnight background, “Sweldo with a plan.” hero, demo balance card
2. Nav: How it works, For Filipinos, Banks, Security; theme toggle; Log in + Join Beta
3. Scroll: quote band → Filipino comparison → banks → loop + preview image → formulas → security vault block → beta CTA
4. Toggle theme (Settings or landing toggle) — light mode uses Lovable light tokens

### App interior

1. Log in → dashboard stat cards use new primary; headings use Space Grotesk where updated
2. Savings plan → fund balances + pie chart allocation colors
3. Mobile ~375px — landing readable; logged-in bottom nav contrast OK

### Checks

- [ ] No console errors on `/` and dashboard
- [ ] Money displays use `formatMoney` (₱15,000.00 shape)
- [ ] Mail preview still shows teal CTA `#1E8B75`

## Suggested commit

```
Summary: Rebrand app with Lovable tokens and landing page

Port midnight/glow/gold design system, DM Sans + Space Grotesk, and Lovable-exact welcome sections while keeping Settings appearance controls.
```
