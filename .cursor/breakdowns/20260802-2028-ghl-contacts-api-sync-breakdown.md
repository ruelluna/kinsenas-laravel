# GHL Contacts API sync — breakdown

**Date:** 2026-08-02 20:28

## Summary

Replaced GoHighLevel inbound webhooks with the Contacts API (Private Integration Token + location ID). Beta apply/approve/reject and survey submissions now upsert contacts. Survey answers stay in `survey_responses`; GHL receives tags derived from language, persona result, and each answer value.

## Changelog

- Beta registration, approve, and reject upsert a GHL contact tagged `kinsenas-beta` plus status (`beta-pending` / `beta-approved` / `beta-rejected`)
- Survey submit upserts a GHL contact tagged from answers/result (no custom fields / answer payload)
- Config uses `GHL_PIT` + `GHL_LOCATION_ID`; webhook URL env vars removed from example/config
- Sync no-ops unless `GHL_ENABLED` is true and both PIT and location ID are set

## Files touched

### Config
- `config/services.php`
- `.env.example`

### Backend
- `app/Services/Marketing/GhlMarketingService.php`
- `app/Support/Survey/SurveyGhlTagBuilder.php`
- `app/Jobs/SyncSurveyResponseToGhl.php`
- `app/Http/Controllers/Marketing/SurveyResponseController.php`

### Tests
- `tests/Feature/OpenBeta/OpenBetaRegistrationTest.php`
- `tests/Feature/SurveyResponseStoreTest.php`

## Deploy / verify

1. Set in environment: `GHL_ENABLED=true`, `GHL_PIT=…`, `GHL_LOCATION_ID=…`
2. Remove obsolete `GHL_WEBHOOK_*` if still present
3. PIT needs contacts create/edit scope
4. Ensure queue worker is running when `QUEUE_CONNECTION` is not `sync`
5. `vendor/bin/pint --dirty` (already run locally)

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/OpenBeta/OpenBetaRegistrationTest.php tests/Feature/SurveyResponseStoreTest.php
```

## Visual QA (manual)

N/A — no UI change. Optional smoke: complete `/survey` and register under open beta with GHL enabled; confirm contact + tags in GHL.

## Suggested application commit

```
Summary: Sync beta and survey leads to GHL via Contacts API

Replace inbound webhooks with Private Integration upserts so beta
applications and survey responses create/update contacts with tags.
Survey answer values are tagged, not stored as custom fields.
```

## Linear paste block

```
Title: Sync beta and survey leads to GHL via Contacts API

Description:
Beta apply/approve/reject and public survey submissions upsert GoHighLevel contacts using the Private Integration Token. Survey answers remain in our database; GHL gets tags for language, persona, and each answer value.

Comment / instructions:
Set GHL_ENABLED=true, GHL_PIT, GHL_LOCATION_ID. Remove old GHL_WEBHOOK_* vars. Queue worker required in production. Suggested: php artisan test --compact tests/Feature/OpenBeta/OpenBetaRegistrationTest.php tests/Feature/SurveyResponseStoreTest.php
```
