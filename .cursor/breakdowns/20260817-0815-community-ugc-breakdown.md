# Community UGC (Plan 3 v1) — breakdown

**Date:** 2026-08-17

## Summary

Shipped member-only Community UGC: categories, story submission with moderation, published feed, reports, and admin tools. Comments deferred to v1.1; guest access and non-admin moderators out of scope.

## Changelog

- **Learn → Community** tab with feed, “My stories”, and submit flow (subscribers only; pending until approved).
- **Admin:** community categories CRUD, moderation queue (approve/reject), open reports list (dismiss/resolve).
- **Report** form on published community post show pages (not on own posts).
- **CommunitySeeder** — Payday wins, Side hustle stories, Tips categories; 2 published + 1 pending sample posts.
- Fixed category update validation (route param `community_category`).

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| Comments | Defer v1.1 | No comment tables or UI in v1 |
| Guest access | Members only | All community routes require auth + verified |
| Moderators | Platform admins only | ManagePlatform permission for categories, queue, reports |

## Files touched

### Backend

- `database/migrations/2026_08_17_080300_create_community_tables.php`
- `app/Enums/CommunityPostStatus.php`, `CommunityReportReason.php`, `CommunityReportStatus.php`
- `app/Models/CommunityCategory.php`, `CommunityPost.php`, `CommunityPostReport.php`
- `database/factories/*Community*.php`
- `app/Services/Content/CommunityPostService.php`, `CommunityModerationService.php`, `CommunityPublishService.php`, `CommunityReportService.php`
- `app/Support/Content/CommunityPresenter.php`
- `app/Http/Controllers/Learn/LearnCommunityController.php`
- `app/Http/Controllers/Admin/AdminCommunityModerationController.php`, `AdminCommunityCategoryController.php`, `AdminCommunityReportController.php`
- `app/Http/Requests/Learn/StoreCommunityPostRequest.php`, `StoreCommunityPostReportRequest.php`
- `app/Http/Requests/Admin/StoreCommunityCategoryRequest.php`, `UpdateCommunityCategoryRequest.php`
- `routes/web.php`, `routes/admin-content.php`
- `database/seeders/CommunitySeeder.php`, `DatabaseSeeder.php`

### Frontend

- `resources/js/components/learn/learn-nav-tabs.tsx`
- `resources/js/components/admin/content-admin-tabs.tsx`
- `resources/js/pages/learn/community/{index,mine,create,show}.tsx`
- `resources/js/pages/admin/content/community-posts/pending.tsx`
- `resources/js/pages/admin/content/community-categories/{index,create,edit}.tsx`
- `resources/js/pages/admin/content/community-reports/index.tsx`

### Tests

- `tests/Unit/Services/Content/CommunityModerationServiceTest.php`
- `tests/Feature/Learn/CommunityPostTest.php`, `CommunityFeedTest.php`, `CommunityReportTest.php`
- `tests/Feature/Admin/CommunityModerationTest.php`, `CommunityCategoryTest.php`
- `tests/Browser/CommunityUgcTest.php`

## Deploy / verify

- `php artisan migrate`
- `npm run dev` or `npm run build` (new Inertia pages)
- Optional: `php artisan db:seed --class=CommunitySeeder` on existing DBs

## Suggested tests

```bash
php artisan test --compact tests/Unit/Services/Content/CommunityModerationServiceTest.php tests/Feature/Learn/CommunityPostTest.php tests/Feature/Learn/CommunityFeedTest.php tests/Feature/Learn/CommunityReportTest.php tests/Feature/Admin/CommunityModerationTest.php tests/Feature/Admin/CommunityCategoryTest.php

php artisan test --compact tests/Browser/CommunityUgcTest.php
```

Browser tests require current Playwright: `npm install playwright@latest && npx playwright install`

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`, migrate + seed

### Member

1. Log in as subscribed member.
2. **Learn → Community** — see published stories; open one; submit a report (not own post).
3. **Share your story** — submit; check **My stories** for pending status.

### Admin

1. Log in as platform admin (`admin@example.com`).
2. **Content → Community categories** — list/create/edit.
3. **Content → Community** — approve pending post.
4. **Content → Reports** — dismiss or resolve after a member report.

### Checks

- [ ] No console errors
- [ ] Light/dark mode on community pages
- [ ] Mobile width usable

## Suggested commit

```
Summary: Add Community UGC with moderation and reports (Plan 3 v1)

Members submit stories for admin review; subscribers browse a moderated feed.
Platform admins manage categories, the approval queue, and member reports.
```
