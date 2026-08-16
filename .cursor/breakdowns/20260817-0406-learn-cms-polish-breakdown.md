# Learn CMS polish — breakdown

**Date:** 2026-08-17

## Summary

Plan 1 near-term polish: dashboard Learn highlights, demo seed wiring, survey → Learn CTA, and Open Graph meta on public post teasers.

## Changelog

- Dashboard shows a **New on Learn** card with the three most recent published posts and a link to `/learn`.
- `ContentSeeder` runs automatically after the platform admin user in `DatabaseSeeder`.
- Payday survey result screen includes a secondary **Explore free guides on Learn** link (EN / TL / CEB).
- Public external post teasers expose `openGraph` props for social sharing (`og:title`, `og:description`, `og:url`, optional `og:image`).

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| Dashboard highlights scope | All published posts | Uses `memberVisible()` — same as member library |
| OG metadata | External/both teasers only | Internal posts omit `openGraph` even for subscribed members |

## Files touched

**Backend**

- `app/Services/Content/LearnHighlightService.php` (new)
- `app/Services/Dashboard/DashboardSummaryService.php`
- `app/Http/Controllers/Learn/LearnPostController.php`
- `database/seeders/DatabaseSeeder.php`

**Frontend**

- `resources/js/components/dashboard/learn-highlights-card.tsx` (new)
- `resources/js/pages/dashboard.tsx`
- `resources/js/types/dashboard.ts`
- `resources/js/pages/learn/posts/show.tsx`
- `resources/js/components/survey/survey-result.tsx`
- `resources/js/lib/survey/survey-types.ts`
- `resources/js/lib/survey/survey-content.ts`

**Tests**

- `tests/Feature/DashboardTest.php`
- `tests/Feature/Learn/PublicLearnTest.php`

## Deploy / verify

- No new migrations.
- Fresh seed: `php artisan migrate:fresh --seed` now includes Learn demo content.
- If frontend not visible: `npm run dev` or `npm run build`.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/DashboardTest.php --filter="learn highlights"
php artisan test --compact tests/Feature/Learn/PublicLearnTest.php --filter="open graph"
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

**Prereqs:** `npm run dev` if frontend changed; run seed or `migrate:fresh --seed` for demo posts.

### Dashboard Learn card

1. Log in as a member with vault unlocked.
2. Open **Dashboard**.
3. Confirm **New on Learn** appears below the setup checklist when published posts exist.
4. Click a post title → opens `/learn/posts/{slug}`.
5. Click **Browse all** → opens `/learn`.

### Survey Learn link

1. Open the payday survey (landing).
2. Complete to the result screen.
3. Confirm **Explore free guides on Learn** (or localized equivalent) links to `/learn`.

### Open Graph (optional)

1. As guest, open an external-scope post teaser.
2. View page source — confirm `og:title`, `og:description`, `og:url` meta tags.

### Checks

- [ ] No console errors on dashboard
- [ ] Learn card hidden when no published posts
- [ ] Internal-only posts have no `openGraph` in Inertia props

## Suggested application commit

```
Summary: Add Learn dashboard highlights and public teaser OG meta

Wire ContentSeeder into default seed, surface recent posts on the dashboard,
link survey results to Learn, and expose Open Graph props for shareable teasers.
```

## Linear paste block

```
Title: Learn CMS polish — dashboard highlights, survey CTA, OG meta

Description:
Dashboard shows recent Learn posts in a "New on Learn" card. ContentSeeder runs on default seed. Survey result links to /learn in all three languages. Public post teasers include Open Graph metadata for sharing.

Comment / instructions:
No migration. Run migrate:fresh --seed for demo content. Visual QA: Dashboard → New on Learn card; survey result → Learn link. Tests: DashboardTest (learn highlights), PublicLearnTest (open graph).
```
