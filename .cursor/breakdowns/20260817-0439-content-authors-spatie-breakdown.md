# Content authors + Spatie permissions breakdown

**Date:** 2026-08-17

## Summary

Finished Spatie role/permission setup for platform access and introduced **Author** as a content role. Authors can access the Content admin area (Posts only), create and manage their own posts, and see author attribution on admin and Learn pages. Platform admins retain full content management including all posts, series, and stats.

## Changelog

- Spatie `laravel-permission` roles seeded via migration + `RolePermissionSeeder`: `platform-admin`, `author`, `user`
- Permissions: `admin.manage-platform` (ops + all content), `admin.manage-content` (author post access)
- Content routes split: posts/uploads/preview require `admin.manage-content`; series/stats require `admin.manage-platform`
- Authors scoped to own posts on index, edit, update, delete, and preview
- Platform admins can assign author on create/edit via author picker
- Admin post list and edit header show author name; Learn pages already show `authorName`
- Sidebar Content group visible to authors (Posts only); Admin ops group remains platform-admin only
- Content admin tabs hide Series/Stats for authors

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| Series access | Admin-only | Authors cannot create or manage series |
| Stats access | Admin-only | Authors cannot view engagement stats |
| Author assignment | Admin picker | Authors always own posts they create |
| Permission model | Two permissions | `manage-content` for authors; `manage-platform` implies all content |

## Roles / permissions matrix

| Role | `admin.manage-platform` | `admin.manage-content` | Posts | Series | Stats | Platform ops |
| ---- | --- | --- | --- | --- | --- | --- |
| **platform-admin** | yes | yes | all | yes | yes | yes |
| **author** | no | yes | own only | no | no | no |
| **user** | no | no | no | no | no | no |

## Files touched

### Backend
- `app/Enums/PlatformRole.php`, `app/Enums/PlatformPermission.php`
- `app/Models/User.php` — `canManageContentPost()`, `canManageAllContent()`
- `app/Http/Requests/Admin/Concerns/AuthorizesContentPost.php` (new)
- `app/Http/Requests/Admin/StoreContentPostRequest.php`, `UpdateContentPostRequest.php`
- `app/Http/Requests/Admin/StoreContentSeriesRequest.php`, `UpdateContentSeriesRequest.php`
- `app/Http/Controllers/Admin/AdminContentPostController.php`
- `app/Http/Controllers/Learn/LearnPostController.php` — preview scoping
- `app/Services/Content/ContentPublishService.php` — author assignment
- `app/Http/Middleware/HandleInertiaRequests.php`
- `routes/admin-content.php`, `routes/web.php`
- `config/permission.php`, migrations, `database/seeders/RolePermissionSeeder.php`

### Frontend
- `resources/js/components/admin/admin-sidebar-nav.tsx`
- `resources/js/components/admin/content-admin-tabs.tsx`
- `resources/js/components/admin/content-post-form-fields.tsx`
- `resources/js/pages/admin/content/posts/index.tsx`, `create.tsx`, `edit.tsx`
- `resources/js/types/auth.ts`, `resources/js/types/content.ts`
- `packages/shared/src/types/auth.ts`

### Tests
- `tests/Feature/Admin/PlatformRoleTest.php`
- `tests/Feature/Admin/ContentPostTest.php`
- `tests/Feature/Admin/ContentSeriesTest.php`

## Deploy / verify

- `php artisan migrate` (permission tables + role migration if not yet run)
- `npm run dev` or `npm run build` for frontend changes
- Assign **Author** role via **Admin → Users** for content writers

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Admin/PlatformRoleTest.php
php artisan test --compact tests/Feature/Admin/ContentPostTest.php
php artisan test --compact tests/Feature/Admin/ContentSeriesTest.php
php artisan test --compact tests/Feature/Admin/ContentUploadTest.php

vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend changed

### Author happy path

1. Log in as a user with **Author** role
2. Confirm sidebar shows **Content → Posts** only (no Admin ops, no Series/Stats tabs)
3. Create a post — confirm it saves and list shows your name as author
4. Confirm you cannot open another author's post edit URL (403)

### Platform admin

1. Log in as platform admin
2. Open **Content → Posts** — see all posts with author names
3. Create/edit post — use **Author** dropdown to assign a writer
4. Confirm **Series** and **Stats** tabs still work

### Checks

- [ ] No console errors
- [ ] Learn post page shows "By {authorName}"
- [ ] Light/dark mode on admin post forms

## Suggested commits

**Spatie / roles (if splitting):**
```
Summary: Add Spatie platform roles and permissions

Seed platform-admin, author, and user roles with manage-platform and
manage-content permissions. Split admin routes by permission middleware.
```

**Author feature:**
```
Summary: Scope content admin for authors with own-post access

Authors manage their own posts in Content admin; platform admins assign
authors and retain full series/stats access. Show author on admin list
and edit forms.
```

## Linear paste block

```
Title: Content authors with Spatie role-based access

Description:
Authors with the author role can access Content admin (Posts tab) and
create/edit/delete their own posts. Platform admins manage all content,
assign authors, and retain series/stats access. Spatie permissions gate
admin routes.

Comment / instructions:
Run migrate if needed. Assign Author role via Admin → Users. Visual QA:
author sees Posts only; admin sees full Content group. Tests:
PlatformRoleTest, ContentPostTest, ContentSeriesTest.
```
