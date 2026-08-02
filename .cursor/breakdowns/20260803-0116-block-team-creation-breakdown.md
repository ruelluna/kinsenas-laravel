# Block additional team creation — breakdown

**Date:** 2026-08-03

## Summary

Users can no longer create additional owned teams (shared workspaces) during beta. Registration still creates the personal team; invitation-based joining is unchanged. Re-enable multi-team later with `TEAMS_ALLOW_ADDITIONAL_OWNED=true`.

## Changelog

- **Settings → Teams** and the sidebar team switcher no longer show **New team** when the user already owns a team
- Teams index shows a short note: additional workspaces are coming soon; invite members instead
- `POST /settings/teams` returns **403** when additional owned teams are disabled
- Config flag `teams.allow_additional_owned_teams` (env: `TEAMS_ALLOW_ADDITIONAL_OWNED`, default `false`)

## Files touched

**Config**

- `config/teams.php` (new)

**Backend**

- `app/Policies/TeamPolicy.php`
- `app/Http/Requests/Teams/SaveTeamRequest.php`
- `app/Http/Controllers/Teams/TeamController.php`
- `app/Actions/Teams/CreateTeam.php`
- `app/Http/Middleware/HandleInertiaRequests.php`

**Frontend**

- `resources/js/components/team-switcher.tsx`
- `resources/js/pages/teams/index.tsx`
- `resources/js/types/global.d.ts`
- `resources/js/types/billing.ts`

**Tests**

- `tests/Feature/Teams/TeamTest.php`
- `tests/Feature/Billing/TeamBillingTest.php`
- `tests/Feature/OpenBeta/OpenBetaAccessTest.php`
- `tests/Feature/Billing/TrialExpiredAccessTest.php`
- `tests/Feature/Admin/PlatformUserAdminTest.php`

## Deploy / migration

No migrations. Optional env when re-enabling:

```
TEAMS_ALLOW_ADDITIONAL_OWNED=true
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Teams/TeamTest.php
php artisan test --compact tests/Feature/Billing/TeamBillingTest.php
php artisan test --compact tests/Feature/Auth/RegistrationTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend changed

### Happy path

1. Log in as a registered user (has default personal team)
2. Open team switcher in sidebar — confirm **no "New team"** option
3. Open **Settings → Teams** — confirm **no "New team"** button and the coming-soon note is visible
4. From **Teams → Edit**, send an invitation — confirm invite flow still works

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] `POST /settings/teams` returns 403 (curl or DevTools)
- [ ] Accept invitation as another user — can join without creating a team

### Regression

- [ ] Login/logout still works
- [ ] Registration still creates personal team and lands on dashboard flow

## Suggested application commit

```
Summary: Block additional owned team creation during beta

Users keep their registration personal workspace and can still join teams
via invitation, but cannot create new shared teams until multi-team is
re-enabled via TEAMS_ALLOW_ADDITIONAL_OWNED.
```

## Linear paste block

```
Title: Block additional owned team creation during beta

Description:
Users cannot create new shared team workspaces while allow_additional_owned_teams is false (default). Personal team at registration and invitation-based joining are unchanged. UI hides New team in switcher and Settings → Teams.

Comment / instructions:
No migrate. Run npm run dev or npm run build if frontend changed. Visual QA: team switcher + Settings → Teams (no New team); invite flow still works. Suggested: php artisan test --compact tests/Feature/Teams/TeamTest.php tests/Feature/Billing/TeamBillingTest.php.
```
