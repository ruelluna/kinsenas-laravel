# Page content padding standardization

**Date:** 2026-08-01

## Summary

Standardized app page padding in a shared `PageContent` wrapper applied by both sidebar and header app layouts. Removed duplicate padding from settings layout and a few pages that defined their own outer spacing.

## Changelog

- All authenticated app pages now share `px-4 py-6 md:px-6` via `PageContent` in the main layout
- Sidebar header horizontal padding aligns with page content (`px-4 md:px-6`)
- Settings/teams nested layout no longer adds its own outer padding (avoids double padding)
- Dashboard, billing pay, and vault unlock no longer set redundant page-level padding

## Files touched

**Layout / components**

- `resources/js/components/page-content.tsx` (new)
- `resources/js/layouts/app/app-sidebar-layout.tsx`
- `resources/js/layouts/app/app-header-layout.tsx`
- `resources/js/components/app-sidebar-header.tsx`
- `resources/js/layouts/settings/layout.tsx`

**Pages (redundant padding removed)**

- `resources/js/pages/dashboard.tsx`
- `resources/js/pages/billing/pay.tsx`
- `resources/js/pages/vault/unlock.tsx`

## Deploy / verify

- `npm run dev` (or `npm run build`) if frontend is not already running

## Suggested tests (run manually)

No PHP changes — visual QA only.

```bash
npm run dev
```

## Visual QA (manual)

**URL:** http://financial-literacy.test
**Prereqs:** `npm run dev`

### Happy path

1. Log in as any user with team access
2. Open **Dashboard** — content should have even spacing from sidebar and edges
3. Open **Settings → Profile** — no double padding vs other pages
4. Open **Teams** — heading and list align with sidebar header breadcrumbs
5. Open a savings page (e.g. **Savings Plan**) — content no longer flush against edges
6. Open **Billing → Pay** and **Vault unlock** if applicable — centered forms keep max-width without extra outer padding

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Breadcrumb row aligns horizontally with page content below
- [ ] Light and dark mode
- [ ] Mobile width (~375px): padding remains readable, sidebar toggle works

### Regression

- [ ] Login/logout still works
- [ ] Settings sub-nav pages (Profile, Security, Appearance) layout unchanged aside from consistent padding

## Suggested application commit

```
Summary: Standardize app page content padding in shared layout

Introduce PageContent wrapper in app layouts so all current and future pages inherit consistent px-4/py-6 (md:px-6) spacing. Remove duplicate padding from settings layout and individual pages.
```
