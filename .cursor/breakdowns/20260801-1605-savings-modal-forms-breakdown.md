# Savings modal forms breakdown

**Date:** 2026-08-01

## Summary

Moved inline create forms on Income, Transfers, Spending, Banks, and Recipients index pages into modals triggered by header **Add …** buttons. Transfers and Spending fund-card shortcuts open the same modal with the fund pre-selected. Backend routes and controllers unchanged.

## Changelog

- Income, Banks, and Recipients: **Add …** button top-right opens a modal; inline bordered form removed from index
- Transfers and Spending: **Add transfer** / **Add spending** shown only when locked income exists; fund card buttons open modal with fund preset
- Transfers: cross-bank confirmation dialog preserved inside modal submit flow
- Spending: optional bank details collapsible moved into modal
- Banks: GoTyme GoSave setup state lives in modal component

## Files touched

### New components

- `resources/js/components/savings/add-income-modal.tsx`
- `resources/js/components/savings/add-transfer-modal.tsx`
- `resources/js/components/savings/add-spending-modal.tsx`
- `resources/js/components/savings/add-bank-modal.tsx`
- `resources/js/components/savings/add-recipient-modal.tsx`

### Updated pages

- `resources/js/pages/savings/income/index.tsx`
- `resources/js/pages/savings/transfers/index.tsx`
- `resources/js/pages/savings/spending/index.tsx`
- `resources/js/pages/savings/banks/index.tsx`
- `resources/js/pages/savings/recipients/index.tsx`

## Deploy / verify

- No migrations
- Run `npm run dev` (or `npm run build`) to pick up frontend changes
- Manual check: http://financial-literacy.test

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
php artisan test --compact tests/Feature/Savings/FundTransferTest.php
php artisan test --compact tests/Feature/Savings/FundSpendTest.php
php artisan test --compact tests/Feature/Savings/GoTymeBankSetupTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Happy path

1. Log in as a user with a savings plan and vault unlocked
2. **Income** → click **Add income** → fill period + amount → **Save income** → period appears, modal closes
3. Lock an income period
4. **Transfers** → **Add transfer** → record same-bank transfer → list updates
5. Record cross-bank transfer → confirmation dialog appears → confirm → pending transfer in list
6. Click **Transfer from {fund}** on a fund card → modal opens with fund pre-selected
7. **Spending** → **Add spending** → submit → list updates; test **Spend from {fund}** shortcut
8. Expand **Bank details (optional)** inside spending modal
9. **Banks** → **Add bank** → standard bank and GoTyme GoSave flows
10. **Recipients** → **Add recipient** → list updates

### Checks

- [ ] No inline form cards on any of the five index pages
- [ ] **Add …** buttons appear top-right next to page title
- [ ] Cancel closes modal without saving
- [ ] No console errors (DevTools → Console)
- [ ] Light and dark mode
- [ ] Mobile width (~375px): modal scrolls when content is tall

### Regression

- [ ] Income Lock / Unlock still works
- [ ] Transfers / Spending **Confirm** on pending items still works
- [ ] Income show page (breakdown) unchanged

## Suggested application commit

```
Summary: Move savings create forms into modals

Replace inline bordered forms on Income, Transfers, Spending, Banks, and
Recipients index pages with Add … header buttons that open dialog modals.
Fund card shortcuts on Transfers and Spending pre-select the fund in the
modal. No backend changes.
```

## Linear paste block

```
Title: Move savings create forms into modals

Description:
Income, Transfers, Spending, Banks, and Recipients index pages now use
Add … header buttons that open create forms in dialogs instead of inline
bordered cards. Transfers and Spending fund-card shortcuts open the same
modal with the fund pre-selected.

Comment / instructions:
Run npm run dev after deploy. Visual QA: each savings sidebar item → Add
button → modal submit → list updates. Suggested tests: IncomePeriodTest,
FundTransferTest, FundSpendTest, GoTymeBankSetupTest.
```

---

## Changelog (2026-08-01) — Spending receipt upload

- Optional receipt image on **Record spending** modal (`receipt_image` field, max 5 MB)
- Mobile (coarse pointer): **Take a photo of your receipt** opens rear camera via `capture="environment"`
- Desktop: file picker with image preview before submit
- Receipts stored on `public` disk under `spending-receipts/`; thumbnail shown in recent activity list
- Migration: `receipt_image_path` on `fund_spends`

### Additional files

- `database/migrations/2026_07_31_200820_add_receipt_image_path_to_fund_spends_table.php`
- `resources/js/components/savings/receipt-upload-field.tsx`
- `app/Models/FundSpend.php`, `FundSpendService.php`, `FundSpendController.php`, `SaveFundSpendRequest.php`
- `tests/Feature/Savings/FundSpendTest.php` — receipt upload test

### Deploy

- Run `php artisan migrate`
- Run `npm run dev`

### Visual QA (receipt)

1. **Spending** → **Add spending** → tap receipt area on phone → camera opens
2. Capture/select image → preview appears → **Record spending** → thumbnail in list
3. Tap thumbnail → full image opens in new tab

### Suggested commit

```
Summary: Add optional receipt photo upload to spending

Store receipt images on fund spends with mobile camera capture in the
Add spending modal. Show receipt thumbnails in the spending activity list.
```

---

## Changelog (2026-08-01) — Remove bank details from Record spending

- Removed **Bank details (optional)** collapsible from Add spending modal
- **Recipient (optional)** dropdown now inline (default **None**); no bank picker on the form
- Stopped loading `banks` and `categoryBankMap` on spending index page
- New spends from UI auto-confirm (pending only when `bank_id` is set via API/legacy data)
