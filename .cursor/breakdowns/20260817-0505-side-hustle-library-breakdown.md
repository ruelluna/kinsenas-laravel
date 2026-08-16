# Plan 2 — Side Hustle Library & Podcasts — breakdown

**Date:** 2026-08-17

## Summary

Shipped **Plan 2**: a structured **Side Hustle Library** with admin-managed categories and a separate **Podcast** entity (shows + episodes). Reuses Learn access gates and publish scope from Plan 1.

## Changelog

- New tables: `side_hustle_categories`, `side_hustles`, `podcast_shows`, `podcast_episodes`
- Admin **Content** nav: Side hustles, Hustle categories, Podcasts, Podcast episodes
- Member routes: `/learn/side-hustles`, `/learn/podcasts` with category filter and structured hustle detail panel
- Podcasts are **not** `content_posts` — dedicated show/episode CRUD
- Demo seed: street food cart, VA guide, Sweldo Stories podcast (2 episodes)
- Learn hub links to Side hustles and Podcasts

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Podcasts | Separate `podcast_shows` + `podcast_episodes` | Not overloaded on Learn posts/series |
| Side hustle categories | Dedicated table, one category per hustle (v1) | Filter chips on library index |
| Tips to save | Defer | Keep Plan 1 reminder posts |
| Reactions/stats | Defer | No helpful toggle on hustles/episodes v1 |

## Files touched

### Backend
- Migration, models, enums, factories, `LearnLibraryPublishService`, `LearnLibraryPresenter`
- Admin controllers + form requests
- Learn controllers + routes
- `LearnLibrarySeeder`, `DatabaseSeeder`

### Frontend
- Admin pages (categories, hustles, podcast shows/episodes)
- `side-hustle-form-fields.tsx`, expanded `content-admin-tabs.tsx`, `admin-nav.ts`
- Learn pages: side-hustles index/show, podcasts index/show/episode
- `resources/js/types/learn-library.ts`

## Deploy steps

```bash
php artisan migrate
php artisan db:seed --class=LearnLibrarySeeder
npm run build
```

## Suggested tests

```bash
php artisan test --compact tests/Feature/Admin/SideHustleCategoryTest.php tests/Feature/Admin/SideHustleTest.php tests/Feature/Admin/PodcastShowTest.php tests/Feature/Admin/PodcastEpisodeTest.php tests/Feature/Learn/SideHustleLibraryTest.php tests/Feature/Learn/PodcastLibraryTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `php artisan migrate`, `npm run build`

### Admin

1. Log in as platform admin
2. **Content → Hustle categories** → create **Food & beverage**
3. **Content → Side hustles** → create entry with capital tier, skills, TipTap body
4. **Content → Podcasts** → create show → **Podcast episodes** → add episode with embed URL

### Member

1. Open **Learn** → **Side hustles** → filter by category → open detail (capital, skills, guide)
2. **Learn → Podcasts** → open show → play episode / read show notes (subscribed member)

### Checks

- [ ] Category filter updates library list
- [ ] Guest sees external teaser only; internal hustles 404
- [ ] Podcast episode belongs to correct show URL
- [ ] No console errors

## Suggested commit

```
Summary: Add side hustle library and podcast entities (Plan 2)

Structured side hustle catalog with categories and admin CRUD, plus separate podcast shows/episodes. Member library at /learn/side-hustles and /learn/podcasts reuses Learn access gates.
```
