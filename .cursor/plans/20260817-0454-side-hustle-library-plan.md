---
name: Plan 2 — Side Hustle Library & Podcasts
overview: "Structured side hustle catalog with admin-managed categories, plus podcasts as a separate show/episode entity. Reuses Learn access gates and publish scope from Plan 1."
todos:
  - id: impact-analysis
    content: "Impact matrix — migrations, admin nav, Learn routes, seeders, tests"
    status: completed
  - id: resolve-conflicts
    content: "Podcasts = own entity (not content_posts); side hustles = dedicated tables with categories"
    status: completed
  - id: failing-feature-tests
    content: "RED — admin CRUD + member browse/show for side hustles, categories, podcasts"
    status: in_progress
  - id: failing-browser-tests
    content: "RED — browser smoke for admin create hustle + member library filter"
    status: pending
  - id: implement
    content: "GREEN — migrations, models, admin + member UI"
    status: pending
  - id: update-regression-tests
    content: "Fix Learn/Content tests if routes or nav props change"
    status: pending
  - id: scoped-tests-green
    content: "Run in-scope Feature + Browser tests"
    status: pending
isProject: true
---

# Plan 2 — Side Hustle Library & Podcasts

Parent: [education_content_cms Plan 1](../../C:/Users/ruell/.cursor/plans/education_content_cms_2383b03d.plan.md) (Learn CMS shipped)

## Problem

Plan 1 delivers generic Learn posts/series. Members need a **filterable side hustle library** (street food → VA, etc.) with structured comparison fields, and a **podcast section** that is not overloaded onto generic posts.

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Side hustles storage | Dedicated `side_hustles` table | Not `content_posts.metadata` |
| Categories | `side_hustle_categories` — admin CRUD | Each hustle belongs to one category (v1) |
| Podcasts | Separate `podcast_shows` + `podcast_episodes` | Not `content_series` / `content_posts` |
| Tips to save | Defer | Keep using Plan 1 `reminder` posts |
| Publish scope | Reuse `ContentPublishScope` | Same teaser vs full member access |
| Status | Reuse `ContentPostStatus` pattern | draft / published / archived |
| Reactions / stats | Defer | No helpful toggle on hustles/episodes in v1 |
| Author scoping | Platform admin only for categories & podcasts; side hustles use `admin.manage-content` | Authors do not manage library v1 |
| Capital display | Tier enum + optional min/max PHP amounts | Filter by tier; show `formatMoney` range on detail |

## Data model

### `side_hustle_categories`

| Column | Notes |
| ------ | ----- |
| `id` | UUID PK |
| `name`, `slug` | Unique slug |
| `description` | Nullable |
| `sort_order` | Admin ordering |
| `status` | published / draft |

### `side_hustles`

| Column | Notes |
| ------ | ----- |
| `side_hustle_category_id` | FK, required |
| `title`, `slug` | Unique slug |
| `excerpt` | Teaser |
| `body` | TipTap HTML guide |
| `cover_image_url` | Optional |
| `difficulty` | beginner / intermediate / advanced |
| `capital_tier` | low / moderate / high |
| `startup_capital_min`, `startup_capital_max` | Nullable integers (PHP major units) |
| `time_commitment_hours_min`, `time_commitment_hours_max` | Nullable |
| `skills`, `equipment` | JSON string arrays |
| `publish_scope`, `status`, `published_at` | Same as posts |

### `podcast_shows`

| Column | Notes |
| ------ | ----- |
| `title`, `slug` | Show / feed |
| `description`, `cover_image_url` | |
| `status`, `published_at`, `sort_order` | |

### `podcast_episodes`

| Column | Notes |
| ------ | ----- |
| `podcast_show_id` | FK |
| `episode_number` | Unique per show |
| `title`, `slug` | Global unique slug |
| `excerpt`, `show_notes` | HTML show notes |
| `audio_embed_url` | Spotify / Apple / iframe URL |
| `duration_minutes` | Optional |
| `publish_scope`, `status`, `published_at` | |

## Routes

### Admin (`routes/admin-content.php`)

- `content/side-hustle-categories` — resource (platform admin)
- `content/side-hustles` — resource (manage-content)
- `content/podcast-shows` — resource (platform admin)
- `content/podcast-episodes` — resource (platform admin)

### Member / public (`routes/web.php`)

- `GET /learn/side-hustles` — index with category filter
- `GET /learn/side-hustles/{sideHustle}` — detail
- `GET /learn/podcasts` — shows index
- `GET /learn/podcasts/{podcastShow}` — show + episodes
- `GET /learn/podcasts/{podcastShow}/episodes/{podcastEpisode}` — episode detail

## Admin nav

Extend **Content** group:

- Side hustles
- Hustle categories
- Podcasts (shows)
- Podcast episodes

## Member UX

- Learn hub links to **Side hustles** and **Podcasts**
- Side hustle index: category chips + cards (capital tier, difficulty badges)
- Side hustle show: structured summary panel + HTML body + disclaimer
- Podcast show: episode list; episode show: embed + show notes

## Seeders

`SideHustleSeeder` + `PodcastSeeder` (or combined `LearnLibrarySeeder`):

- Categories: Food & beverage, Online work, Services
- 3–4 sample hustles (e.g. street food, VA, online tutoring)
- 1 podcast show, 2 episodes

## Tests (TDD)

| Layer | File |
| ----- | ---- |
| Feature | `tests/Feature/Admin/SideHustleCategoryTest.php` |
| Feature | `tests/Feature/Admin/SideHustleTest.php` |
| Feature | `tests/Feature/Admin/PodcastShowTest.php` |
| Feature | `tests/Feature/Admin/PodcastEpisodeTest.php` |
| Feature | `tests/Feature/Learn/SideHustleLibraryTest.php` |
| Feature | `tests/Feature/Learn/PodcastLibraryTest.php` |
| Browser | `tests/Browser/SideHustleLibraryTest.php` |

## Out of scope (Plan 2 v1)

- Member reactions on hustles/episodes
- Full-text search
- Trilingual content
- RSS / Apple Podcasts sync
- Community UGC (Plan 3)

## Impact matrix

| Area | Affected? | Notes |
| ---- | --------- | ----- |
| Database | Yes | 4 new tables |
| Models & factories | Yes | 4 models |
| Seeders | Yes | Demo library data |
| Routes & Wayfinder | Yes | Admin + learn routes |
| Admin nav | Yes | 4 new items |
| Learn index | Yes | Cross-links |
| Tests | Yes | New Feature + Browser |
| Mobile API | N/A | Deferred |
| Docs | N/A | Not requested |
