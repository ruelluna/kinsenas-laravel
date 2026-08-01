# Kinsenas landing page breakdown

**Date:** 2026-08-01

## Summary

Replaced the minimal welcome hero at `/` with a full emotional, Filipino-focused marketing landing page. The page positions Kinsenas as a payday savings planner that helps Filipinos split income before spending starts — warm tone, no bank/crypto aesthetic.

## Changelog (user-visible)

- Home page (`/`) now shows a multi-section landing: hero, emotional problem, how it works, formula demo, privacy/trust, outcomes, and final CTA
- Hero headline: **Sweldo ngayon. May matitira bukas.** with payday split visual (₱15,000 → Bills, Savings, Giving, Family, Emergency, Goals)
- Primary CTA **Create My Kinsenas Plan** links to registration; **See How It Works** scrolls to the 3-step section
- Formula section shows TRC / Abundant-style positioning with a sample ₱15,000 split bar
- Privacy section explains encrypted vault — calm, not scary
- Footer weaves taglines: *Bago maubos, itabi na.*, *Sweldo with a plan.*, etc.
- Logged-in users see Dashboard in header; guest CTAs hidden when authenticated

### 2026-08-01 — Design polish (natural / modern)

- Shared `landing-section.tsx` for consistent spacing, typography, and eyebrow pills
- Hero: radial gradient orbs, pill taglines, rounded-full CTAs, softer payday visual with tinted bucket cards (no heavy borders)
- How it works: open step layout with connecting line instead of boxed cards
- Formula: segmented bar with gaps, tinted category rows
- Privacy: horizontal panel with icon + copy (no nested card chrome)
- Final CTA: inset rounded panel with soft gradient
- Footer: taglines as individual chips instead of one long dot-separated line
- Header: lighter blur bar, rounded nav buttons

## Files touched

### Created

- `resources/js/components/marketing/landing-content.ts` — shared demo data, copy arrays
- `resources/js/components/marketing/landing-header.tsx`
- `resources/js/components/marketing/landing-hero.tsx`
- `resources/js/components/marketing/payday-split-visual.tsx`
- `resources/js/components/marketing/landing-emotional-problem.tsx`
- `resources/js/components/marketing/landing-how-it-works.tsx`
- `resources/js/components/marketing/landing-formula-section.tsx`
- `resources/js/components/marketing/landing-privacy.tsx`
- `resources/js/components/marketing/landing-outcome.tsx`
- `resources/js/components/marketing/landing-final-cta.tsx`
- `resources/js/components/marketing/landing-footer.tsx`
- `tests/Feature/WelcomePageTest.php`

### Modified

- `resources/js/pages/welcome.tsx` — composes all marketing sections
- `resources/css/app.css` — `scroll-behavior: smooth` on `html`

## Deploy / verify

- No migrations
- No Wayfinder regen (no route changes)
- Run `npm run dev` or `npm run build` if frontend does not hot-reload

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/WelcomePageTest.php
php artisan test --compact tests/Feature/ExampleTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`) if frontend changed

### Happy path

1. Open `/` as guest
2. Confirm hero headline and payday split visual (6 buckets)
3. Click **See How It Works** — page scrolls to 3-step section
4. Click **Create My Kinsenas Plan** — lands on `/register`
5. Scroll through problem, formula bar, privacy, outcome, final CTA sections

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Light and dark mode: allocation colors and text readable
- [ ] Mobile width (~375px): hero stacks copy above visual; header usable
- [ ] Logged-in user: header shows Dashboard; hero/final CTAs hidden

### Regression

- [ ] Login/logout still works
- [ ] Register flow unchanged

## Suggested application commit

```
Summary: Add emotional Filipino landing page for Kinsenas home

Replace the minimal welcome hero with a full marketing page focused on
payday splitting before money disappears. Uses existing design tokens,
logo assets, and register CTAs; no backend or route changes.
```

## Linear paste block

```
Title: Add emotional Filipino landing page for Kinsenas home

Description:
The home page at / is now a full landing experience with hero, emotional
problem, how-it-works, formula demo, privacy, outcomes, and final CTA.
Copy is Tagalog/English mix; CTAs link to registration.

Comment / instructions:
Run npm run dev or npm run build. Visual QA: / as guest — hero, anchor
scroll, register CTA. Suggested: php artisan test --compact tests/Feature/WelcomePageTest.php
```
