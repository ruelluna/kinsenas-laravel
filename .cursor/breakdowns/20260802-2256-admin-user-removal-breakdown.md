# Admin user removal

**Date:** 2026-08-02

## Summary

Platform admins can permanently remove user accounts from **Admin → Users**. Deletion cancels owned-team subscriptions, soft-deletes owned workspaces (including personal teams), clears GHL tags, and invalidates sessions. Guardrails block self-deletion, removing the last platform admin, and deleting owners of shared teams that still have other members.

## Changelog

- **Users** admin page (formerly “Platform admins” in the sidebar) lists all users with grant/revoke admin and **Remove user**
- Email confirmation modal required before deletion
- Owned personal and sole-member shared teams are removed with the user; subscriptions are cancelled
- Users who own a shared team with other members cannot be deleted until ownership is transferred or members are removed

## Files touched

### Backend

- `app/Services/Users/UserDeletionService.php` — orchestration, guardrails, team/subscription cleanup
- `app/Http/Controllers/Admin/AdminPlatformUserController.php` — `destroy`, deletion block reasons on index
- `app/Http/Requests/Admin/DeletePlatformUserRequest.php` — platform admin auth + email confirmation
- `app/Support/Marketing/GhlTagCatalog.php` — `allStaticTags()` for account deletion
- `routes/admin.php` — `DELETE admin/platform-users/{user}`

### Frontend

- `resources/js/pages/admin/platform-users/index.tsx` — remove button, block reasons, page title “Users”
- `resources/js/components/admin/delete-platform-user-modal.tsx` — confirmation dialog
- `resources/js/components/admin/admin-sidebar-nav.tsx` — sidebar label “Users”
- `resources/js/types/billing.ts` — `deleteBlockReason` on `AdminPlatformUser`

### Tests

- `tests/Feature/Admin/PlatformUserAdminTest.php` — destroy happy path, guardrails, email confirmation

## Deploy / verify

- No migrations
- `php artisan wayfinder:generate --no-interaction` — if using Wayfinder for admin routes later
- `npm run dev` or `npm run build` — frontend changed

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Admin/PlatformUserAdminTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend not built

### Happy path

1. Log in as a platform admin
2. Open **Admin → Users**
3. Find a non-admin test user (not yourself)
4. Click **Remove user**, type their email, confirm
5. Confirm success toast and user no longer appears in the list

### Checks

- [ ] **Remove user** disabled for your own account
- [ ] **Remove user** disabled for last platform admin
- [ ] Block message shown for shared-team owner with other members
- [ ] Email mismatch shows validation error in modal
- [ ] No console errors

### Regression

- [ ] Grant/revoke platform admin still works
- [ ] **Admin → Subscribers** unchanged

## Suggested commit

```
Summary: Add admin user account removal

Platform admins can permanently delete users from Admin → Users with email
confirmation. Deletion cancels subscriptions, removes owned workspaces, and
blocks unsafe cases (self, last admin, shared-team owner with members).
```

## Linear paste block

```
Title: Add admin user account removal

Description:
Platform admins can remove user accounts from Admin → Users. Email confirmation is required. Owned personal/sole-member teams and subscriptions are cleaned up; shared teams with other members block deletion.

Comment / instructions:
Run npm run dev after deploy. Visual QA: Admin → Users → Remove user on a test account. Suggested: php artisan test --compact tests/Feature/Admin/PlatformUserAdminTest.php
```
