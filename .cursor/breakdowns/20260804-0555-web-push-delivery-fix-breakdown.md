# Web Push delivery fix breakdown

**Date:** 2026-08-04

## Summary

Fixed likely Web Push delivery blockers on beta: unified service worker registration (no broken `registerSW.js` precache), corrected push content encoding to `aes128gcm`, hardened subscribe flow, added failure logging, and admin/CLI diagnostics.

## Changelog

- VitePWA uses manual SW registration only (`injectRegister: null`); `registerSW.js` excluded from precache
- Push subscribe stores `aes128gcm` (Firefox/Mozilla endpoints still use `aesgcm`)
- Subscribe waits for service worker registration before `pushManager.subscribe`
- Settings → Notifications shows device/server push status and toast after enable
- Admin → Push test shows VAPID/subscription checklist
- `notifications:send-test-push` prints target checklist before send
- Failed Web Push sends logged via `LogWebPushNotificationFailed`

## Deploy steps (beta)

```bash
npm run build
php artisan config:cache
# ensure queue worker is running
```

After deploy, users should **unregister the old service worker** (DevTools → Application → Service Workers → Unregister) and re-enable browser push in Settings.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Notifications/WebPushSubscriptionTest.php
php artisan test --compact tests/Feature/Notifications/PushNotificationDiagnosticsTest.php
php artisan test --compact tests/Feature/Notifications/SendTestPushCommandTest.php
vendor/bin/pint --dirty
npm run build
```

## Visual QA (manual)

**URL:** https://beta.kinsenas.ph

1. DevTools → Application → Service Workers — confirm `/sw.js` active, no `registerSW.js` 404
2. **Settings → Notification settings** → **Enable browser push** → confirm toast + status lines
3. Save per-event push toggles
4. **Admin → Push test** → review checklist → send to self → bell entry + OS notification

## Suggested commit

```
Summary: Fix Web Push delivery on beta (SW, encoding, diagnostics)

Stop precaching missing registerSW.js, use aes128gcm subscriptions, register SW
before subscribe, log push failures, and add admin/CLI readiness checklists.
```
