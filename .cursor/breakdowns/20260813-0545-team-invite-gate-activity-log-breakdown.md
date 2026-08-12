# Team invite setup gate + Spatie activity logging

**Date:** 2026-08-13

## Summary

Team invitations now require banks, a savings plan, and income on the team before owners/admins can invite members. App-wide user actions are logged with `spatie/laravel-activitylog` through a privacy-first wrapper that records who did what without storing financial amounts or encrypted values.

## Changelog

- Invite gate enforced in policy, form request, and team edit UI (banks + plan + income; spending not required)
- Installed `spatie/laravel-activitylog` with UUID morph columns on `activity_log`
- Added `UserActivityLogger`, `ActivityPropertySanitizer`, and `UserActivityAction` enum
- Logged team/invite lifecycle (send, cancel, accept, decline, expire prune, member role/remove/leave, team CRUD/switch)
- Admin **Activity logs** index with filters; team edit shows recent team-scoped activity
- Extended logging to banks, profile updates, and platform user admin actions

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Invite setup gate | Banks + plan + income (no spending) | Dashboard checklist still includes spending for onboarding |
| Activity logging | Spatie + central sanitizer | No amounts, encrypted fields, or change diffs with money |
| Log visibility | Admin index + team edit feed | No member-global log page in v1 |

## Files touched

**Backend:** `TeamSetupService`, `TeamInviteReadiness`, audit services/enum, `TeamPolicy`, `CreateTeamInvitationRequest`, team controllers (web + API), `DashboardSummaryService`, `routes/console.php`, `routes/admin.php`, `AdminActivityLogController`, `BankController`, `ProfileController`, `AdminPlatformUserController`, migration/config for activity log

**Frontend:** `teams/edit.tsx`, `admin/activity-logs/index.tsx`, `admin-nav.ts`, `types/teams.ts`

**Tests:** Unit/feature/browser tests for setup gate, sanitizer, logger, activity logs; updated `TeamInvitationTest`, `PruneExpiredTeamInvitationsTest`, `Pest.php` helpers

## Deploy / verify

- `composer install` (adds `spatie/laravel-activitylog`)
- `php artisan migrate`
- `npm run build` if frontend not built via dev server
- Visual QA below

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Unit/Services/Teams/TeamSetupServiceTest.php
php artisan test --compact tests/Unit/Services/Audit/
php artisan test --compact tests/Feature/Teams/TeamInvitationSetupGateTest.php
php artisan test --compact tests/Feature/Teams/TeamActivityLogTest.php
php artisan test --compact tests/Feature/Admin/AdminActivityLogTest.php
php artisan test --compact tests/Browser/TeamInviteSetupGateTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

### Happy path

1. Log in as a team owner with banks, plan, and income on the team
2. Open **Settings → Teams → Edit team**
3. Confirm **Invite member** is enabled; send an invite
4. Open **Admin → Activity logs** (platform admin) and confirm invite row appears without amount fields

### Checks

- [ ] Incomplete setup (missing bank/plan/income): invite button disabled with setup tooltip
- [ ] Complete setup: invite works
- [ ] Team edit **Recent activity** lists invite send/cancel events
- [ ] No console errors

## Suggested application commit

```
Summary: Gate team invites on setup and add privacy-first activity logging

Team owners and admins must complete banks, plan, and income before inviting members. User actions are recorded via spatie/laravel-activitylog with a sanitizer that excludes financial amounts and encrypted data; admins can review logs and teams see recent activity on edit.
```
