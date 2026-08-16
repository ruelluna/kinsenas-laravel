# Learn CMS (Plan 1) — breakdown

**Date:** 2026-08-17

## Summary

Shipped admin-owned **Learn** content hub: posts, series, public teasers, member library, helpful reactions, and admin engagement stats. Foundation for future Side Hustle Library (Plan 2) and Community UGC (Plan 3).

## Changelog

- Platform admins manage **content series** and **posts** under Admin → Content
- Members and guests browse `/learn`; guests see external/both teasers only
- Subscribed members read full markdown body, react helpful, navigate series episodes
- Admin **Content stats** dashboard: views, unique viewers, reactions, top posts (7d / 30d / all)
- **Learn** in member nav (including billing-only users) and landing header
- Engagement events pruned after 365 days (scheduled)

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Community UGC | Plan 3 | Members cannot post in Plan 1 |
| Categories / podcasts / side hustles | Plan 2 | Generic post types only |
| Vault gate | Exempt | Learn routes skip vault unlock |
| Stats | Views in events; reactions in table | No duplicate reaction event rows |

## Files touched

**Backend:** migrations, enums, models, factories, services, middleware, controllers, form requests, routes, seeder, console schedule

**Frontend:** `learn/*` pages, admin content CRUD, `ContentBody`, `LearnPageLayout`, nav updates, landing header

**Tests:** Unit (engagement, stats), Feature (admin, public, member, reactions), Browser `LearnTest`

## Deploy

```bash
php artisan migrate
npm run build   # if frontend not built in CI
```

Optional seed: `php artisan db:seed --class=ContentSeeder`

## Suggested tests

```bash
php artisan test --compact tests/Unit/Services/Content/
php artisan test --compact tests/Feature/Admin/ContentSeriesTest.php tests/Feature/Admin/ContentPostTest.php
php artisan test --compact tests/Feature/Learn/
php artisan test --compact tests/Browser/LearnTest.php
vendor/bin/pint --dirty
```

## Visual QA

**URL:** http://financial-literacy.test/learn

### Happy path

1. Log in as subscribed member → **Learn** in More menu
2. Open a published post → full body, helpful button
3. Click **Mark helpful** → count updates
4. Log out → `/learn` shows teasers only; external post shows register CTA
5. Platform admin → **Admin → Content** → create/edit post, preview draft

### Checks

- [ ] Guest marketing shell on `/learn`; member app shell when logged in
- [ ] Internal posts hidden from public index
- [ ] Series episode prev/next links
- [ ] Financial disclaimer footer on post show

## Suggested commit

```
Summary: Add Learn CMS for admin editorial content

Introduces posts, series, public teasers, helpful reactions, and admin stats at /learn. Platform admins publish via admin/content; members consume without vault unlock.
```
