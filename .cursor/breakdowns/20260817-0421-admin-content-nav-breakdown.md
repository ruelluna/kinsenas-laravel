# Admin Content nav group — breakdown

**Date:** 2026-08-17

## Summary

Split platform-admin navigation into **Admin** (ops) and **Content** (Posts, Series, Stats) groups. Added in-page tabs on content admin index pages so Series is discoverable without guessing URLs.

## Changelog

- Sidebar shows separate **Admin** and **Content** groups for platform admins only.
- **Content** group: Posts, Series, Stats (Series was missing from nav before).
- Mobile **More** sheet mirrors both groups.
- Posts / Series / Stats index pages share `ContentAdminTabs` with `data-test` selectors.
- Browser smoke: admin can switch from Posts to Series via tabs.

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| Group label | Content | User confirmed vs Learn |
| Series discoverability | Nav item + page tabs | `/admin/content/series` linked in sidebar and tabs |

## Files touched

**Frontend**

- `resources/js/types/navigation.ts` — `NavGroup` type
- `resources/js/lib/admin-nav.ts` — `adminOpsNavItems`, `contentNavItems`, `adminNavGroups`
- `resources/js/components/admin/admin-sidebar-nav.tsx` — two sidebar groups, active states
- `resources/js/components/mobile/mobile-more-sheet.tsx` — two mobile admin sections
- `resources/js/components/admin/content-admin-tabs.tsx` (new)
- `resources/js/pages/admin/content/posts/index.tsx`
- `resources/js/pages/admin/content/series/index.tsx`
- `resources/js/pages/admin/content/stats/index.tsx`

**Tests**

- `tests/Browser/AdminContentNavTest.php` (new)

## Deploy / verify

- No migration or backend changes.
- Run `npm run dev` or `npm run build` if frontend not visible in Herd.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Admin/ContentPostTest.php tests/Feature/Admin/ContentSeriesTest.php
php artisan test --compact tests/Browser/AdminContentNavTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** Log in as platform admin (`admin@example.com` after seed)

### Sidebar

1. Open any member page with sidebar expanded.
2. Confirm **Admin** group (Subscribers, Plans, Users, …) and **Content** group (Posts, Series, Stats).
3. Click **Series** → **Content series** page with **New series** button.
4. Log in as non-admin member → neither admin group visible.

### Content tabs

1. Open **Admin → Content → Posts** (or `/admin/content/posts`).
2. Confirm tabs: Posts (active), Series, Stats.
3. Click **Series** tab → series index; **Stats** tab → stats index.

### Mobile

1. Narrow viewport (~375px) → **More** → **Admin** and **Content** sections with same links.

### Checks

- [ ] Active highlight on Series when on `/admin/content/series/create`
- [ ] No console errors

## Suggested application commit

```
Summary: Split admin nav into Content group with Series link

Give platform admins a dedicated Content sidebar group (Posts, Series, Stats),
mirror it on mobile More, and add shared tabs on content admin index pages.
```

## Linear paste block

```
Title: Admin Content nav group — Posts, Series, Stats

Description:
Platform admins now see separate Admin and Content sidebar groups. Content includes Posts, Series, and Stats. Content admin index pages share tabs for quick switching. Series is no longer URL-only.

Comment / instructions:
No migration. npm run dev if UI stale. Visual QA: sidebar Content → Series; tabs on /admin/content/posts. Tests: ContentPostTest, ContentSeriesTest, AdminContentNavTest.
```
