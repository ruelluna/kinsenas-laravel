# Custom bank entry + edit banks & recipients

**Date:** 2026-08-04

## Summary

Users can add banks not in the seeded Philippine institution catalog, and edit or remove banks and recipients from the Savings UI. Backend routes already existed for update/delete; this ships the missing Inertia modals and list actions.

## Changelog

- **Add bank:** Search dropdown includes **Other bank…** and **Use "{query}"** when no catalog match; custom banks submit by name only (no institution link).
- **Banks list:** Each bank card has **Edit** and **Remove** actions; edit supports custom bank name + account label, institution-linked banks keep institution read-only.
- **Recipients list:** Each row has **Edit** and **Remove**; shared add/edit modal for type, name, and notes.
- **Remove confirmations:** Delete modals warn that fund-bucket links (banks) or spending links (recipients) are cleared, history remains.
- **Validation:** Trim bank/recipient names on save; bank update limited to fillable fields.

## Files touched

### Frontend

- `resources/js/components/savings/bank-institution-picker.tsx`
- `resources/js/components/savings/add-bank-modal.tsx`
- `resources/js/components/savings/edit-bank-modal.tsx` (new)
- `resources/js/components/savings/delete-bank-modal.tsx` (new)
- `resources/js/components/savings/recipient-form-modal.tsx` (new)
- `resources/js/components/savings/delete-recipient-modal.tsx` (new)
- `resources/js/components/savings/add-recipient-modal.tsx` (re-export)
- `resources/js/pages/savings/banks/index.tsx`
- `resources/js/pages/savings/recipients/index.tsx`

### Backend

- `app/Http/Requests/Savings/SaveBankRequest.php`
- `app/Http/Requests/Savings/SaveRecipientRequest.php`
- `app/Http/Controllers/Savings/BankController.php`

### Tests

- `tests/Feature/Savings/BankStoreTest.php` (new)
- `tests/Feature/Savings/RecipientStoreTest.php` (new)

## Deploy / migration

No migrations. After deploy:

- `npm run build` (or `npm run dev` locally) for frontend changes.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/BankStoreTest.php
php artisan test --compact tests/Feature/Savings/RecipientStoreTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend not built

### Banks — custom add

1. Log in as a team member.
2. Open **Savings → Banks** → **Add bank**.
3. Search for a name not in the list (e.g. `My Credit Union`).
4. Click **Use "My Credit Union"** or **Other bank…**, set account label, submit.
5. Confirm the new bank appears with a letter avatar (no logo).

### Banks — edit & remove

1. On **Banks**, click **Edit** on a custom bank; change name/label → **Save changes**.
2. Click **Edit** on an institution bank; confirm institution is read-only, label updates.
3. Click **Remove** → confirm dialog → bank disappears from list.

### Recipients — edit & remove

1. Open **Savings → Recipients** → **Add recipient** (smoke).
2. Click **Edit** on a row; change type/name/notes → **Save changes**.
3. Click **Remove** → confirm → recipient removed from list.

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Light and dark mode on modals
- [ ] Mobile width (~375px): edit/remove icon buttons usable
- [ ] Custom bank assignable on **Savings plan** category bank dropdown

### Regression

- [ ] Add institution bank (e.g. BDO) still works
- [ ] GoTyme GoSave setup still works when selecting GoTyme

## Suggested application commit

```
Summary: Add custom banks and edit/remove banks and recipients

Users can enter banks outside the seeded catalog, edit account labels
and custom bank names, and manage recipients from list actions. Backend
validation trims names; bank update stays scoped to safe fields.
```

## Linear paste block

```
Title: Custom bank entry + edit banks and recipients

Description:
Members can add banks not in the PH institution catalog via Other/use-query
in Add bank. Banks and Recipients index pages now support edit and remove
with confirmation dialogs. Custom banks show letter avatars; institution
banks keep linked branding on edit.

Comment / instructions:
Run npm run build after deploy. Visual QA: Banks → Add custom → Edit/Remove;
Recipients → Edit/Remove. Suggested: php artisan test --compact tests/Feature/Savings/BankStoreTest.php tests/Feature/Savings/RecipientStoreTest.php
```
