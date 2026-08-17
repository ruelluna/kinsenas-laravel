# Content admin entity navigation

**Date:** 2026-08-17

## Summary

Reorganized admin content UI around five entities (Posts, Series, Podcasts, Side Hustles, Community), each with **List | Settings | Stats** sub-navigation. Taxonomy and moderation moved into entity Settings; podcast episodes nested under show edit; global stats split per entity.

## Changelog

- **ContentEntityTabs** — two-row nav (5 entity tabs + List/Settings/Stats); authors see Posts and Side Hustles list only
- **Posts** — settings hub embeds post categories; stats at `/admin/content/posts/stats`
- **Series** — settings stub; stats with series count and aggregated episode views
- **Podcasts** — list at `/admin/content/podcasts`; episodes managed from show edit; nested create route
- **Side hustles** — settings hub embeds categories; stats by category
- **Community** — list at `/admin/content/community`; settings hub (categories + moderation + reports); stats by status
- **Sidebar** — Content group reduced to 5 items
- **Redirects** — legacy URLs (`/stats`, `/post-categories`, `/community-posts`, `/podcast-shows`, `/podcast-episodes`, etc.) redirect to new paths

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| Community moderation/reports | All in Settings hub | Single `/admin/content/community/settings` page |
| Podcast episodes | Nested under show edit | Global episodes index redirects to podcasts list |
| Category CRUD routes | Retained | Create/edit forms unchanged; index redirects to settings |

## Files touched

### Backend
- `routes/admin-content.php`
- `app/Http/Controllers/Admin/*` (posts, series, podcasts, side hustles, community, settings)
- `app/Services/Content/SeriesStatsService.php`
- `app/Services/Content/SideHustleStatsService.php`
- `app/Services/Content/PodcastStatsService.php`
- `app/Services/Content/CommunityStatsService.php`
- `app/Http/Requests/Admin/StorePodcastEpisodeRequest.php`

### Frontend
- `resources/js/components/admin/content-entity-tabs.tsx`
- `resources/js/lib/admin-nav.ts`
- `resources/js/pages/admin/content/posts/settings.tsx`, `posts/stats.tsx`
- `resources/js/pages/admin/content/series/settings.tsx`, `series/stats.tsx`
- `resources/js/pages/admin/content/podcasts/settings.tsx`, `podcasts/stats.tsx`
- `resources/js/pages/admin/content/side-hustles/settings.tsx`, `side-hustles/stats.tsx`
- `resources/js/pages/admin/content/community/index.tsx`, `settings.tsx`, `stats.tsx`
- Updated entity list/create/edit pages to use `ContentEntityTabs`
- `resources/js/pages/admin/content/podcast-shows/edit.tsx` — episodes section

### Tests
- `tests/Feature/Admin/ContentAdminNavigationTest.php` (new)
- Updated: `CommunityPostAdminTest`, `CommunityModerationTest`, `ContentPostCategoryTest`, `SideHustleCategoryTest`, `PodcastShowTest`, `PlatformRoleTest`, `AdminContentNavTest`, `CommunityUgcTest`

## Deploy / verify

- No migration
- `npm run dev` if frontend changes not visible
- Visual QA below

## Suggested tests

```bash
php artisan test --compact tests/Feature/Admin/ContentAdminNavigationTest.php
php artisan test --compact tests/Feature/Admin/CommunityPostAdminTest.php
php artisan test --compact tests/Feature/Admin/CommunityModerationTest.php
php artisan test --compact tests/Browser/AdminContentNavTest.php
vendor/bin/pint --dirty
```

Browser tests require Playwright: `npx playwright install`

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Happy path

1. Log in as platform admin
2. Open **Content → Posts** — confirm two-row nav (Posts selected, List active)
3. Click **Settings** — post categories list visible
4. Click **Stats** — posts engagement stats
5. Click **Podcasts** entity tab → edit a show → confirm **Episodes** section with Add episode
6. Click **Community** → **Settings** — categories, moderation queue, open reports sections

### Checks

- [ ] Sidebar Content group shows exactly 5 items
- [ ] Authors see only Posts + Side hustles entity tabs (no Settings/Stats)
- [ ] Old URLs redirect (e.g. `/admin/content/stats` → posts stats)
- [ ] No console errors

## Suggested commit

```
Summary: Reorganize content admin around five entity tabs

Replace the flat 12-tab bar with Posts, Series, Podcasts, Side Hustles, and
Community — each with List, Settings, and Stats. Nest podcast episodes under
show edit; aggregate community moderation into Settings.
```
