# Savings plan guidance (admin-editable) — breakdown

**Date:** 2026-08-01

## Summary

Members choosing a savings formula now see admin-editable intro copy, optional videos, per-template “best for” text, and category purpose descriptions. After the first income entry, a “What you can change” panel explains locked percentages vs editable custom categories. Platform admins manage content at `/admin/savings-plan-guidance` and `/admin/formula-templates`.

## Changelog

- New singleton `savings_plan_page_guidance` for chooser intro, before-choose note, and post-income rules (+ optional video URLs)
- Extended formula templates with `best_for`, `video_embed_url`; template categories with `description`
- Admin pages to edit page guidance and per-template/category content
- Savings Plan picker: intro block, before-choose alert, rich template cards with percentage bar and category descriptions
- Plan editor: before-choose reminder (no income yet) and post-income rules panel with fixed rules table
- `VideoEmbed` component normalizes YouTube/Vimeo URLs

## Files touched

**Backend**
- Migration, `SavingsPlanPageGuidance` model
- `SavingsFormulaTemplate`, `SavingsFormulaTemplateCategory` fillable updates
- `VideoEmbedUrl` validation rule
- Admin controllers, form requests, `routes/admin.php`
- `SavingsPlanController` — `pageGuidance` + enriched `templates` props
- Seeders: `SavingsPlanPageGuidanceSeeder`, updated `SavingsFormulaTemplateSeeder`, `DatabaseSeeder`

**Frontend**
- `resources/js/types/savings.ts`
- `resources/js/lib/video-embed-url.ts`
- `resources/js/components/savings/video-embed.tsx`
- `resources/js/components/savings/plan-guidance-panels.tsx`
- `resources/js/components/savings/plan-template-picker.tsx`
- `resources/js/pages/savings/plan.tsx`
- `resources/js/pages/admin/savings-plan-guidance/edit.tsx`
- `resources/js/pages/admin/formula-templates/index.tsx`
- `resources/js/pages/admin/formula-templates/edit.tsx`

**Tests**
- `tests/Feature/Admin/SavingsPlanGuidanceTest.php`

## Deploy steps

```bash
php artisan migrate
php artisan db:seed --class=SavingsPlanPageGuidanceSeeder
php artisan db:seed --class=SavingsFormulaTemplateSeeder
php artisan wayfinder:generate --no-interaction
npm run dev
vendor/bin/pint --dirty
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Admin/SavingsPlanGuidanceTest.php
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test

### Template chooser (no plan)

1. Log in as a user **without** a savings plan
2. Open **Savings Plan** in the sidebar
3. Confirm intro text, **Before you choose** alert, and two template cards with category descriptions
4. Log in as platform admin (`admin@example.com` after fresh seed)
5. Open `/admin/savings-plan-guidance` — edit intro; paste a YouTube URL — save
6. Open `/admin/formula-templates` → edit TRC → add category descriptions and template video URL
7. Return to member Savings Plan — confirm updated copy and video embed

### Post-income editor

1. User with plan + at least one income period → **Savings Plan**
2. Confirm **What you can change** panel, rules table, percentage rows **Locked**
3. Confirm **Add custom category** still works

### Checks

- [ ] No console errors
- [ ] Light and dark mode on new panels
- [ ] Invalid video URL rejected on admin save

## Suggested application commit

```
Summary: Add admin-editable savings plan guidance and richer formula picker

Platform admins can edit chooser copy, videos, and per-category descriptions.
Members see expanded template cards and a post-income rules panel explaining
which plan fields lock after the first income entry.
```
