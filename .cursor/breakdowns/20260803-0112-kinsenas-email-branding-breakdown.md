# Kinsenas branded email templates

**Date:** 2026-08-03

## Summary

Published Laravel markdown mail views and restyled them with Kinsenas teal theme colors and the horizontal logo. All notification emails (team invite, verify email, password reset) now share consistent branding automatically via the shared mail layout.

## Changelog

- Team invitation, email verification, and password reset emails show the Kinsenas horizontal logo in the header
- Email buttons, links, borders, and footer use the app’s teal primary color (`#0D7377`)
- Centralized mail-safe brand colors and logo paths in `config/brand.php`
- Added feature tests asserting rendered HTML includes logo URL and primary color

## Files touched

### Config
- `config/brand.php` (new)

### Mail views
- `resources/views/vendor/mail/html/message.blade.php`
- `resources/views/vendor/mail/html/themes/default.css`
- `resources/views/vendor/mail/html/*` (published vendor copies)
- `resources/views/vendor/mail/text/*` (published vendor copies)

### Frontend (comment only)
- `resources/js/lib/brand.ts` — cross-reference to PHP config

### Tests
- `tests/Feature/Mail/BrandedMailTemplateTest.php` (new)

## Deploy steps

- No migration required
- Ensure production `APP_URL` matches the public site URL so logo images resolve in inboxes

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Mail/BrandedMailTemplateTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**Prereqs:** Trigger an email via Herd (`http://financial-literacy.test`) with `MAIL_MAILER=log` or Resend configured

### Happy path

1. **Forgot password** — request reset link; confirm email shows Kinsenas logo and teal CTA button
2. **Register unverified user → resend verification** — confirm branded layout
3. **Teams → invite member** — confirm invitation email branding

### Checks

- [ ] Logo loads (not broken image)
- [ ] CTA button is teal `#0D7377` with white label
- [ ] Body card, borders, and footer match app feel
- [ ] Plain-text part still readable

## Suggested commit

```
Summary: Brand notification emails with Kinsenas logo and teal theme

Publish Laravel mail views and restyle with config-driven brand colors so team invites, verification, and password reset emails match the app.
```

## Linear paste block

```
Title: Brand notification emails with Kinsenas logo and teal theme

Description:
Published Laravel markdown mail views with Kinsenas horizontal logo and teal primary color. Applies automatically to team invitations, email verification, and password reset notifications.

Comment / instructions:
Ensure APP_URL is correct in production for logo URLs. Visual QA: trigger forgot-password, verification resend, or team invite and confirm logo + teal CTA. Suggested: php artisan test --compact tests/Feature/Mail/BrandedMailTemplateTest.php
```
