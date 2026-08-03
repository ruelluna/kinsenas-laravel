# Notification click URL fix

**Date:** 2026-08-04

## Summary

Push notification and inbox clicks no longer 404 on beta. The app dashboard lives at `/{team}/dashboard`, but notifications used `/dashboard` as a fallback action URL. Added `/launch` and a legacy `/dashboard` redirect, updated notification payloads, and hardened the service worker plus inbox UI to rewrite stale URLs.

## Changelog

- New `/launch` route (`PwaLaunchController`) sends authenticated users to their current team dashboard (or teams settings when no team).
- Legacy `GET /dashboard` (auth) redirects to `/launch` for old bookmarks and stored notification URLs.
- `NotificationActionUrl` helper centralizes `/launch` and team dashboard paths.
- All notifications that previously defaulted to `/dashboard` now use `/launch` or a team-scoped path.
- Service worker `sw-push.js` rewrites `/dashboard` paths (relative or absolute) before `openWindow`.
- Inbox click handler rewrites legacy `/dashboard` action URLs client-side.
- Admin push test and CLI test push default action URL is `/launch`.

## Files touched

**Backend**

- `app/Support/Notifications/NotificationActionUrl.php` (new)
- `app/Http/Controllers/PwaLaunchController.php`
- `routes/web.php` — `/launch`, `/dashboard` redirect
- `app/Notifications/**` — action URL updates
- `app/Console/Commands/Notifications/SendTestPushCommand.php`

**Frontend**

- `public/sw-push.js`
- `resources/js/lib/notification-action-url.ts` (new)
- `resources/js/components/notifications/notification-list.tsx`
- `resources/js/pages/admin/notifications-test/index.tsx`

**Tests**

- `tests/Feature/PwaLaunchTest.php`
- `tests/Feature/Notifications/TeamInvitationNotificationTest.php`

## Deploy / verify

- `npm run build` — ships updated `sw-push.js` (bundled into `sw.js` on deploy)
- `npm run dev` or `npm run build` if frontend changed
- Manual: send admin test push → click OS notification → lands on dashboard (not 404)
- Manual: click same notification in bell inbox → same destination
- Existing DB notifications with `/dashboard` still work via SW rewrite + redirect

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/PwaLaunchTest.php
php artisan test --compact tests/Feature/Notifications/TeamInvitationNotificationTest.php

vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test (or beta after deploy)  
**Prereqs:** `npm run build` if service worker changed; push enabled in Settings

### Happy path

1. Log in as a user with push enabled.
2. **Admin → Push test** → send test to self.
3. Click the **OS notification** → confirm team dashboard loads (no 404).
4. Open bell → click the same notification → same destination.

### Checks

- [ ] No console errors on notification click
- [ ] Legacy inbox item with old `/dashboard` URL still opens dashboard

### Regression

- [ ] `/launch` while logged out → login page
- [ ] Direct `/{team}/dashboard` still works

## Suggested application commit

```
Summary: Fix notification click URLs that 404 on team-scoped dashboard

Push and inbox notifications used /dashboard but the app routes dashboard
under /{team}/dashboard. Route /launch and rewrite legacy URLs in the SW
and inbox so clicks land on the correct page.
```

## Linear paste block

```
Title: Fix notification click URLs (404 on push/inbox click)

Description:
Notification action URLs pointed at /dashboard, which does not exist in the
team-scoped router. Added /launch redirect helper, legacy /dashboard redirect,
updated notification payloads, and SW/inbox rewrites for stored URLs.

Comment / instructions:
Run npm run build on deploy so sw-push.js updates. Visual QA: Admin push test
→ click OS notification and bell inbox entry. Suggested:
php artisan test --compact tests/Feature/PwaLaunchTest.php
```
