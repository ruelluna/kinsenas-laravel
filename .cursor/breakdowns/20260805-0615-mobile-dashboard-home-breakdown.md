# Mobile dashboard home — breakdown

**Date:** 2026-08-05

## Summary

Implemented full mobile Home tab parity with the web dashboard using the existing `GET /api/v1/teams/{team}/dashboard` API. The screen uses mobile-first section ordering: onboarding checklist when setup is incomplete, stat cards and financial sections when complete, attention-first pending actions, pull-to-refresh, recovery key banner, and team invitation sheet.

## Changelog

- Home tab shows setup checklist with dismiss, progress, and Continue setup CTA
- Three summary stat cards (remaining, in banks, needs attention) when plan setup is complete
- Horizontal quick action chips and plan/income/reports links
- Fund balance cards with percent used and Record spending when drawable
- Pending actions with Confirm via API; recent activity when spending exists
- “You’re caught up” quiet state when nothing needs attention
- Recovery key banner after register (SecureStore / web localStorage)
- Pending team invitations bottom sheet with accept/decline
- Pull-to-refresh and error retry on dashboard fetch

## Files touched

### Mobile app

- `apps/mobile/app/(app)/(tabs)/dashboard.tsx` — orchestration
- `apps/mobile/app/(auth)/register.tsx` — recovery key persistence
- `apps/mobile/components/dashboard/*` — section components
- `apps/mobile/lib/dashboard-routes.ts` — web href → Expo route mapper
- `apps/mobile/lib/dashboard-storage.ts` / `.web.ts` — dismiss + recovery key storage
- `apps/mobile/package.json` — `expo-clipboard`

## Deploy / verify

- No backend migrations
- `cd apps/mobile && npm install` (new `expo-clipboard`)
- `cd apps/mobile && npm run types:check`

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Api/V1/DashboardTest.php
cd apps/mobile && npm run types:check
cd apps/mobile && npx expo start
```

## Visual QA (manual)

**Prereqs:** Log in, unlock vault, `npx expo start`

### Happy path

1. Open **Home** tab
2. Confirm setup checklist or stat cards match team state
3. Pull down to refresh dashboard
4. If pending spend/transfer exists, tap **Confirm** and verify item clears after refresh
5. Tap quick actions (Add income, Record spending) and verify navigation

### Checks

- [ ] Money formatted as `₱X,XXX.XX`
- [ ] Spending/transfer actions disabled when plan cannot draw from funds
- [ ] Reports link hidden when subscription lacks reports feature
- [ ] No console errors on web or native

### Regression

- [ ] Bottom tab navigation still works
- [ ] Register flow still shows recovery key sheet; Home banner after skip

## Suggested application commit

```
Summary: Implement mobile Home dashboard with web parity sections

Adds setup checklist, stat cards, fund balances, pending confirm,
recent activity, invitations sheet, and recovery key banner on the
Home tab using the existing dashboard API with mobile-first ordering.
```

## Linear paste block

```
Title: Implement mobile Home dashboard with web parity sections

Description:
Mobile Home tab now mirrors the web dashboard: setup checklist, summary
stats, fund balances, pending confirm actions, recent activity, quick
actions, invitations sheet, and post-register recovery key banner.

Comment / instructions:
Run npm install in apps/mobile for expo-clipboard. Visual QA: Home tab
after login. Suggested: php artisan test --compact tests/Feature/Api/V1/DashboardTest.php
```
