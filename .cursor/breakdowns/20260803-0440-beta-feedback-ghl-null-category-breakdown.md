# Beta feedback GHL null category — breakdown

**Date:** 2026-08-03 04:40

## Summary

Fixed production 500 on beta feedback submit when users left the default category selected. Empty category was stored as `null`, then GHL tag sync crashed on `$feedback->category->value` before any GHL API call.

## Changelog

- Empty or missing feedback category defaults to `general` in validation
- Controller uses safe category fallback before building GHL tags
- Feedback form defaults to `general` and removes duplicate empty option
- Tests cover empty/missing category and GHL tag sync on feedback submit

## Files touched

### Backend
- `app/Http/Requests/Settings/StoreBetaFeedbackRequest.php`
- `app/Http/Controllers/Settings/BetaFeedbackController.php`

### Frontend
- `resources/js/pages/settings/feedback.tsx`

### Tests
- `tests/Feature/OpenBeta/BetaFeedbackTest.php`

## Deploy / verify

No migration. Deploy app + frontend build if production uses compiled assets.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/OpenBeta/BetaFeedbackTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test
**Prereqs:** `npm run dev` if frontend changed

### Happy path

1. Log in as an approved beta user
2. Open **Settings** → **Beta feedback**
3. Leave category as **General feedback** (default)
4. Enter a message and click **Send feedback**
5. Confirm success toast appears (no 500 error)

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Admin **Beta feedback** inbox shows the new submission with category General

## Suggested application commit

```
Summary: Fix beta feedback crash when category is empty

Default empty feedback category to general so GHL tag sync no longer
dereferences null on the default form selection.
```

## Linear paste block

```
Title: Fix beta feedback crash when category is empty

Description:
Production users hit a 500 when submitting beta feedback with the default
category. Empty category was null; GHL tag line called ->value on null.
Now defaults to general in validation, controller, and form.

Comment / instructions:
Deploy app + npm build if needed. Visual QA: Settings → Beta feedback →
default category → submit → success toast. Suggested:
php artisan test --compact tests/Feature/OpenBeta/BetaFeedbackTest.php
```
