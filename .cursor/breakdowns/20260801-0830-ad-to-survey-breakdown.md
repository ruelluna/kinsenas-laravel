# Ad-to-Survey Flow — Breakdown

**Date:** 2026-08-01

## Summary

Added a public, mobile-first `/survey` page for ad traffic. The flow is a client-side wizard: language selection (EN/TL/CEB) → intro → 10 questions with 2 interstitials → loading animation → personalized result → email capture → thank-you. Answers stay in React state; final payload logs to the browser console for future backend wiring.

## Changelog

- Public route at `/survey` (named `survey`) with no auth required
- Language picker: English, Tagalog, Bisaya — all subsequent copy follows selected language
- 10-question guided survey with progress bar (“Question X of 10”)
- Interstitial empathy screens after Q3 and Q6
- Multi-select on Q5; Q8 optional (skip-friendly)
- 3-step “building your plan” loading sequence before results
- 7 result personas with prioritized scoring logic
- Email + optional name capture on result screen
- Console log of full submission payload on CTA submit

## Files touched

### Backend / routes
- `routes/web.php` — `Route::inertia('/survey', 'marketing/survey')`

### Frontend — page
- `resources/js/pages/marketing/survey.tsx`

### Frontend — lib
- `resources/js/lib/survey/survey-types.ts`
- `resources/js/lib/survey/survey-content.ts`
- `resources/js/lib/survey/survey-scoring.ts`
- `resources/js/lib/survey/survey-navigation.ts`

### Frontend — components
- `resources/js/components/survey/survey-shell.tsx`
- `resources/js/components/survey/survey-language-select.tsx`
- `resources/js/components/survey/survey-intro.tsx`
- `resources/js/components/survey/survey-question.tsx`
- `resources/js/components/survey/survey-option-card.tsx`
- `resources/js/components/survey/survey-interstitial.tsx`
- `resources/js/components/survey/survey-nav.tsx`
- `resources/js/components/survey/survey-loading.tsx`
- `resources/js/components/survey/survey-result.tsx`
- `resources/js/components/survey/survey-thank-you.tsx`

### App config
- `resources/js/app.tsx` — layout bypass for `marketing/*`

### Tests
- `tests/Feature/SurveyPageTest.php`

## Deploy / verify

- No migrations
- Run `npm run dev` or `npm run build` if frontend not hot-reloading
- Visit `http://financial-literacy.test/survey`

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/SurveyPageTest.php
vendor/bin/pint --dirty
npm run build
```

## Visual QA (manual)

**URL:** http://financial-literacy.test/survey  
**Prereqs:** `npm run dev` (or `npm run build`) if frontend changed

### Happy path

1. Open `/survey` on mobile width (~375px)
2. Select **Tagalog** — confirm all copy switches to Tagalog
3. Tap **Magpatuloy** through intro and Q1–Q3
4. Confirm interstitial after Q3, then continue through Q4–Q6
5. Confirm second interstitial after Q6
6. Q5: select multiple cards; Q8: skip with no selections
7. Complete Q9–Q10 → loading animation (3 steps) → result screen
8. Enter email, tap **Sumali sa Early Access** → thank-you
9. DevTools → Console: confirm `[Kinsenas Survey]` payload

### Checks

- [ ] No console errors
- [ ] Progress bar updates per question
- [ ] Continue disabled until required answers filled
- [ ] Back preserves prior answers
- [ ] Light and dark mode readable

## Suggested commit

```
Summary: Add public ad-to-survey flow at /survey

Mobile-first Inertia wizard with EN/TL/CEB copy, 10 questions,
interstitials, result scoring, and console-logged lead payload for
future backend integration.
```

## Survey response persistence (2026-08-01)

- Added `survey_responses` table (UUID PK, JSON `answers`, indexed `language`/`result`/`email`)
- Public `POST /survey/responses` with throttle (10/min), validation, and logging
- Frontend submits via Inertia `useHttp().post()` instead of console-only logging

### New backend files
- `app/Models/SurveyResponse.php`
- `app/Enums/SurveyLanguage.php`, `app/Enums/SurveyResultSlug.php`
- `app/Support/Survey/SurveyAnswerOptions.php`
- `app/Http/Controllers/Marketing/SurveyResponseController.php`
- `app/Http/Requests/Marketing/StoreSurveyResponseRequest.php`
- `database/factories/SurveyResponseFactory.php`
- `database/migrations/2026_08_01_004519_create_survey_responses_table.php`
- `tests/Feature/SurveyResponseStoreTest.php`

### Deploy
- Run `php artisan migrate`

### Suggested tests
```bash
php artisan test --compact tests/Feature/SurveyResponseStoreTest.php
php artisan test --compact tests/Feature/SurveyPageTest.php
```


```
Title: Add public ad-to-survey flow at /survey

Description:
Public /survey page for ad traffic: language pick, 10-question wizard,
2 interstitials, loading sequence, 7 scored result personas, email
capture, thank-you. Client-side state only; payload logs to console.

Comment / instructions:
No migrations. Run npm run dev/build. Visual QA: /survey on mobile,
complete flow in Tagalog, verify console payload. Suggested: php artisan test --compact tests/Feature/SurveyPageTest.php
```
