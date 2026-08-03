# Web notifications and PWA push breakdown

**Date:** 2026-08-04

## Summary

Shipped database-backed in-app notifications with a header bell, settings preferences, scheduled reminders, and Web Push for the PWA (no Expo/mobile). Notifications always persist in the inbox; email and push respect per-user toggles.

## Changelog

- Bell icon in app header with unread badge, dropdown inbox, and `/notifications` history page
- **Settings → Notifications** for email, in-app, and push toggles
- Team invitations, pending spends/transfers, low fund balance, and trial-ending reminders
- Web Push subscription flow + service worker push/click handlers
- Daily scheduled reminder commands

## Files touched

### Backend
- Migrations: `notifications`, `user_notification_preferences`, `push_subscriptions`
- Models: `UserNotificationPreference`; `User` + `HasPushSubscriptions`
- Controllers: `NotificationController`, `Settings/NotificationPreferenceController`
- Notifications: `Teams/TeamInvitation`, `Savings/*`, `Billing/TrialEndingReminder`
- Services: `NotificationPreferenceService`, `PendingActionNotificationService`
- Commands: `notifications:pending-actions-reminder`, `notifications:low-fund-balance`, `notifications:trial-ending-reminder`
- Routes: `routes/web.php`, `routes/settings.php`, `routes/console.php`

### Frontend
- `notification-bell.tsx`, `notification-list.tsx`
- `pages/notifications/index.tsx`, `pages/settings/notifications.tsx`
- `lib/web-push.ts`, `types/notifications.ts`
- `app-sidebar-header.tsx`, `settings/layout.tsx`
- `public/sw-push.js`, `vite.config.ts` (Workbox `importScripts`)

### Tests
- `tests/Feature/Notifications/*`
- `tests/Feature/Settings/NotificationPreferencesTest.php`

## Deploy steps

```bash
php artisan migrate
php artisan wayfinder:generate --no-interaction
php artisan webpush:vapid   # add keys to .env
npm run build
```

`.env` keys: `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`

### Windows / Herd: `webpush:vapid` fails with "Unable to create the key"

PostgreSQL ODBC often sets `OPENSSL_CONF` to its own `openssl.cnf`, which breaks PHP EC key generation. Point OpenSSL at Herd's config for that command:

```powershell
$env:OPENSSL_CONF = "$env:USERPROFILE\.config\herd\openssl.cnf"
php artisan webpush:vapid
```

Or show keys without writing `.env`:

```powershell
$env:OPENSSL_CONF = "$env:USERPROFILE\.config\herd\openssl.cnf"
php artisan webpush:vapid --show
```

To fix permanently, remove or update the system/user `OPENSSL_CONF` variable (currently often `C:\Program Files\PostgreSQL\psqlODBC\etc\openssl.cnf`).

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Notifications/
php artisan test --compact tests/Feature/Settings/NotificationPreferencesTest.php
vendor/bin/pint --dirty
npm run build
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` or `npm run build`

### Phase A — In-app inbox

1. Log in as a user with a pending team invitation (or trigger a pending bank-linked spend)
2. Confirm **bell** in header (desktop right; mobile beside team switcher)
3. Unread badge shows count; dropdown lists items; click navigates and marks read
4. Open **Settings → Notifications** — toggle email off for pending actions; verify in-app still works

### Phase B — Web Push

1. Set VAPID keys in `.env`, rebuild frontend
2. **Settings → Notifications** → **Enable browser push** (Chrome desktop or Android)
3. Trigger a pending spend notification from another user/session
4. Confirm OS notification; click opens the correct page
5. Repeat in installed PWA standalone mode if applicable

### Checks

- [ ] No console errors
- [ ] Mark all read clears badge
- [ ] `/notifications` paginates history
- [ ] iOS: in-app bell works; push only after Add to Home Screen

## Suggested application commit

```
Summary: Add web notification inbox and PWA push delivery

Database-backed notifications with header bell, settings preferences,
scheduled reminders, and Web Push for pending actions and billing.
Expo/mobile push remains out of scope.
```

## Linear paste block

```
Title: Add web notification inbox and PWA push delivery

Description:
Shipped in-app notification bell + inbox, settings preferences, and Web Push for the PWA. Covers team invites, pending spends/transfers, low fund balance, and trial-ending reminders. Mobile app push is out of scope.

Comment / instructions:
Run migrate + wayfinder:generate + npm run build. Generate VAPID keys (webpush:vapid) and set VAPID_* in .env before testing push. Visual QA: header bell, Settings → Notifications, enable browser push on Chrome.
Suggested: php artisan test --compact tests/Feature/Notifications/
```
