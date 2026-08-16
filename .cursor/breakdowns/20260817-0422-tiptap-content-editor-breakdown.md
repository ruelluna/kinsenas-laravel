# TipTap content editor — breakdown

**Date:** 2026-08-17

## Summary

Replaced the admin content post markdown textarea with a TipTap WYSIWYG editor. Platform admins can upload images to the local `public` disk; URLs are embedded in post HTML. Member/guest `ContentBody` renders sanitized HTML for new posts and still supports legacy markdown.

## Architecture decisions

| Topic | Choice | Rationale |
| ----- | ------ | --------- |
| Storage format | **HTML** in existing `body` column | TipTap `getHTML()` maps directly to DB; no schema change; easy to render with DOMPurify on read |
| TipTap JSON | Not used | Would require a JSON renderer on learn pages and complicates backward compat |
| Legacy markdown | **Detect on read** (`isHtmlContent`) | Existing factory/plain-text and old markdown posts keep working via `react-markdown` + `rehype-sanitize` |
| Demo seed data | **Converted to HTML** in `ContentSeeder` | Fresh seeds match the new editor output |
| Image storage | `public` disk, `content/images/` | Local-only per scope; returns `asset('storage/...')` URL |
| Upload auth | Platform admin only | Same gate as post CRUD |

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Conflicts | N/A | No overlapping behavior removed |

## User-visible changes

- Admin **Content → Posts** create/edit forms use a rich-text editor (bold, headings, lists, links, images).
- Toolbar **Insert image** uploads to server and embeds the image in the body.
- Learn pages display HTML posts with sanitized markup; markdown posts unchanged.

## Files touched

**Backend**

- `app/Http/Controllers/Admin/AdminContentUploadController.php` (new)
- `app/Http/Requests/Admin/StoreContentUploadRequest.php` (new)
- `routes/admin.php` — `POST admin/content/uploads`

**Frontend**

- `resources/js/components/admin/tiptap-editor.tsx` (new)
- `resources/js/components/admin/content-post-form-fields.tsx`
- `resources/js/components/content/content-body.tsx`
- `resources/js/lib/content-format.ts` (new)

**Data**

- `database/seeders/ContentSeeder.php` — HTML bodies

**Tests**

- `tests/Feature/Admin/ContentUploadTest.php` (new)
- `tests/Feature/Admin/ContentPostTest.php` — HTML body store/update
- `tests/Browser/AdminContentEditorTest.php` (new)

**Dependencies**

- `@tiptap/react`, `@tiptap/starter-kit`, `@tiptap/extension-image`, `@tiptap/extension-link`, `@tiptap/extension-placeholder`, `dompurify`, `@types/dompurify`

## Deploy / verify

- `npm run build` (or `npm run dev`) after pull
- Ensure `php artisan storage:link` exists on the environment (standard Laravel public disk)
- No migrations

## Suggested tests (run manually)

```bash
# Feature tests

php artisan test --compact tests/Feature/Admin/ContentUploadTest.php
php artisan test --compact tests/Feature/Admin/ContentPostTest.php

# Browser tests

php artisan test --compact tests/Browser/AdminContentEditorTest.php
php artisan test --compact tests/Browser/AdminContentNavTest.php

# Lint / format

vendor/bin/pint --dirty
npm run types:check
```

**Note:** `tests/Browser/LearnTest.php` fails with a pre-existing JS error (`Cannot read properties of null (reading 'props')`) unrelated to this change (reproduces with ContentBody markdown-only path).

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` or `npm run build`

### Happy path

1. Log in as a platform admin
2. Open **Content** → **Posts** → **New post**
3. Confirm TipTap toolbar and editor (`data-test="content-body-editor"`)
4. Type formatted text; use **Insert image**; confirm image appears in editor
5. Save post; open **Preview** or member learn URL; confirm HTML renders

### Checks

- [ ] No console errors on create/edit
- [ ] Uploaded image URL loads (`/storage/content/images/...`)
- [ ] Legacy markdown post (if any) still renders on learn
- [ ] Dark mode: editor and learn body readable

## Follow-ups

- **Image cleanup on post delete** — uploaded images are not removed when a post is deleted or an image is removed from the body (orphan files on `public` disk). Consider a later job or reference tracking.
- **Paste/drop image upload** — toolbar upload only today; could extend TipTap `handlePaste` / `handleDrop`.
- **Server-side HTML sanitization** — body is stored as submitted HTML; consider sanitizing on save for defense in depth.
- **LearnTest browser failure** — investigate `usePage`/layout null context separately.

## Suggested commit

```
Summary: Add TipTap WYSIWYG editor and local image uploads for content posts

Replace the admin markdown textarea with TipTap (HTML stored in body).
Platform admins upload images to the public disk; ContentBody renders
sanitized HTML with markdown fallback for legacy posts.
```

## Linear paste block

```
Title: TipTap WYSIWYG editor for Learn CMS posts

Description:
Admin post create/edit uses TipTap instead of a markdown textarea. Body is stored as HTML. Platform admins can upload images to the public disk via POST /admin/content/uploads; URLs embed in the editor. Learn pages render HTML through DOMPurify; markdown posts still work via detection on read. ContentSeeder demo bodies converted to HTML.

Comment / instructions:
Run npm run build after deploy. Visual QA: Content → Posts → New post → insert image → save → preview. Tests: ContentUploadTest, ContentPostTest, AdminContentEditorTest.

Documentation:
N/A
```
