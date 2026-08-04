# Mobile/PWA navigation loading overlay

**Date:** 2026-08-05

## Summary

Added visible loading feedback during Inertia page visits on mobile/PWA. A dimmed content overlay with a centered spinner appears after ~150ms on GET navigations, while the bottom nav highlights the pending destination tab. Desktop keeps an enhanced top progress bar only.

## Changelog

- Mobile content area dims with spinner + “Loading…” during page navigation
- Bottom nav tabs show spinner on the destination tab; other tabs dim while loading
- Partial reloads (e.g. notification badge `only: ['notifications']`) and non-GET visits skip the overlay
- Inertia progress bar tuned (150ms delay, thicker bar on mobile with safe-area offset)
- Auth layout includes the same overlay for post-login redirects on mobile

## Files touched

### Frontend — new

- `resources/js/contexts/navigation-loading-context.tsx`
- `resources/js/components/navigation/navigation-loading-overlay.tsx`

### Frontend — updated

- `resources/js/app.tsx` — `NavigationLoadingProvider`, progress config
- `resources/js/layouts/app/app-sidebar-layout.tsx` — overlay in content wrapper
- `resources/js/layouts/auth/auth-simple-layout.tsx` — overlay on auth pages
- `resources/js/components/mobile/mobile-bottom-nav.tsx` — pending tab feedback
- `resources/css/app.css` — mobile NProgress overrides

## Deploy / migration

None.

## Kinsenas verify

- `npm run dev` (or `npm run build`) — frontend changed
- Manual check at http://financial-literacy.test on mobile width (~375px) and installed PWA if available

## Suggested tests (run manually)

```bash
npm run dev
```

No PHP changes; Pest tests not required for this UI-only pass.

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Happy path

1. Log in as a member with savings access
2. Resize to ~375px width (or use DevTools device mode)
3. Tap **Home → Income → Transfers** via bottom nav
4. Confirm within ~150ms: page content dims, centered spinner + “Loading…” appears
5. Confirm destination tab shows spinner; other tabs dim slightly
6. Confirm new page appears and overlay clears

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Rapid tab switching — overlay updates, never stuck
- [ ] Fast prefetched navigation — overlay may not flash (acceptable)
- [ ] Open sidebar on mobile, tap a link — same overlay behavior
- [ ] Desktop width — no overlay; top progress bar visible
- [ ] Mark notification read — no full-page overlay (partial reload)
- [ ] Light and dark mode

### Regression

- [ ] Login/logout still works
- [ ] Team switcher still completes switch
- [ ] Bottom nav active state still correct after navigation

## Suggested application commit

```
Summary: Add mobile navigation loading overlay during Inertia visits

Dim the content area and show a spinner while page loads on mobile/PWA so navigation no longer feels frozen before the new page appears.
```

## Linear paste block

```
Title: Add mobile navigation loading overlay during Inertia visits

Description:
Mobile/PWA navigation now shows a dimmed content overlay with a centered spinner during GET page visits. Bottom nav highlights the pending destination tab. Partial reloads and non-GET requests are excluded.

Comment / instructions:
Run npm run dev. Visual QA at ~375px: bottom nav between Home/Income/Transfers — overlay appears, clears on page swap. Desktop should show top bar only, no overlay.

Documentation:
N/A
```
