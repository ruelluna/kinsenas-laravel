# User-named default workspace — breakdown

**Date:** 2026-08-01

## Summary

Replaced the forced **Personal** team (and `personal-1` URL slugs) with a user-named default workspace at registration. Internal `is_personal` behavior is unchanged — personal teams still cannot be deleted or left.

## Changelog

- New users get a default workspace named `{name}'s finances` (e.g. "Ruel Luna's finances")
- Default workspace slug derives from the user's name (e.g. `ruel-luna`); numeric suffix only on global slug collision
- Teams index shows a **Default** badge instead of **Personal**
- Migration backfills existing teams still named "Personal"

## Files touched

**Backend**

- `app/Services/Teams/PersonalTeamNaming.php` (new)
- `app/Actions/Teams/CreateTeam.php`
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Models/Team.php` — `uniqueSlugFor()` helper
- `database/factories/UserFactory.php`
- `database/migrations/2026_07_31_221106_rename_personal_teams_to_user_named_workspaces.php`

**Frontend**

- `resources/js/pages/teams/index.tsx`

**Tests**

- `tests/Feature/Auth/RegistrationTest.php`

## Deploy / migration

```bash
php artisan migrate
```

Existing users with `Personal` teams will get new names/slugs. Old bookmarked URLs (`/personal-1/...`) will stop working.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Auth/RegistrationTest.php
php artisan test --compact tests/Feature/Teams/TeamTest.php
php artisan test --compact tests/Feature/Auth/AuthenticationTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `php artisan migrate`, `npm run dev` if frontend not built

### Happy path

1. Register as a new user (e.g. "Ruel Luna")
2. Confirm redirect to `/ruel-luna/dashboard` (not `/personal-1/dashboard`)
3. Open team switcher — shows **Ruel Luna's finances**
4. Open **Settings → Teams** — **Default** badge, no delete/leave for default workspace
5. Create a second team — switcher lists both with meaningful names

### Checks

- [ ] No console errors
- [ ] Team switcher active state correct after switch
- [ ] Light and dark mode on Teams page

### Regression

- [ ] Login/logout still works
- [ ] Leaving/deleting a shared team still falls back to default workspace

## Suggested application commit

```
Summary: Replace forced Personal team with user-named default workspace

New users get "{name}'s finances" with a slug from their display name instead
of personal-1 URLs. Existing Personal teams are backfilled on migrate.
```

## Linear paste block

```
Title: Replace forced Personal team with user-named default workspace

Description:
Registration and factories now create a default workspace named after the user
(e.g. "Ruel Luna's finances" / ruel-luna). The is_personal flag and delete/leave
rules are unchanged. Teams index shows a Default badge instead of Personal.

Comment / instructions:
Run php artisan migrate after deploy. Visual QA: register new user, confirm
/ruel-luna/dashboard URL and team switcher label. Suggested:
php artisan test --compact tests/Feature/Auth/RegistrationTest.php
```
