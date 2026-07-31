# GoTyme GoSave Spaces — breakdown

**Date:** 2026-08-01

## Summary

GoTyme GoSave spaces (main account + up to 5 named spaces) can be set up in one guided flow on the Banks page. Each space is a separate assignable bank record, grouped in category assignment and transfer dropdowns so funds can target specific GoSave buckets.

## Changelog

- Added `bank_institutions.features` JSON and `banks.bank_account_group_id` / `space_role` for grouping spaces
- GoTyme catalog entry includes `savings_spaces` feature (max 5 GoSave)
- Guided GoSave setup on **Banks** when GoTyme is selected
- Category assignment and transfer/spend bank dropdowns show grouped space labels (`GoTyme Bank — Vacation`)
- Banks list groups GoTyme spaces under one collapsible card with combined balance

## Files touched

**Backend:** migration, `BankSpaceRole`, `Bank`, `BankInstitution`, `BankAccountSetupService`, `BankPayloadMapper`, `BankController`, `SaveBankRequest`, plan/transfer/spend controllers, `philippine-bank-institutions.php`, `PhilippineBankSeeder`

**Frontend:** `banks/index.tsx`, `gosave-space-setup.tsx`, `category-bank-select.tsx`, `bank-select.tsx`, `format-bank-label.ts`, `savings.ts`

**Tests:** `GoTymeBankSetupTest.php`, `PhilippineBankSeederTest.php`

## Deploy

```bash
php artisan migrate
npm run dev
vendor/bin/pint --dirty
```

Re-seed or run `PhilippineBankSeeder` on existing environments so GoTyme gets `features`.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/GoTymeBankSetupTest.php
php artisan test --compact tests/Feature/Seeders/PhilippineBankSeederTest.php
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Happy path

1. Log in as a user with vault unlocked
2. Open **Banks** in the sidebar
3. Search and select **GoTyme Bank**
4. Enable two GoSave spaces, name them (e.g. Vacation, Emergency)
5. Click **Add GoTyme account**
6. Confirm grouped GoTyme card with Main + two spaces
7. Open **Savings Plan** → assign Vacation space to one fund, Emergency to another
8. Open **Transfers** → confirm dropdown shows grouped space names

### Checks

- [ ] No console errors
- [ ] Light and dark mode on Banks and Plan pages
- [ ] Non-GoTyme banks still use simple add form

## Suggested commit

```
Summary: Add GoTyme GoSave guided setup for per-space category assignment

Users can configure Main plus up to five GoSave spaces when adding GoTyme.
Each space is assignable to a savings fund and appears grouped in bank pickers.
```
