# Income delete spend guard — breakdown

**Date:** 2026-08-04

## Summary

Income deletion now uses the same remaining-balance formula as spending guards (including opening balances). The income show and index pages expose `deleteBlockReason` so delete is hidden or disabled when confirmed spends/transfers would leave a fund bucket negative after removal.

## Changelog

- `FundBalanceService::assertCanRemovePeriod()` blocks when `openingBalance + allocatedAfterRemoval - transferredOut + receivedIn - spent < 0` for any allocation category
- Added `deleteBlockReasonForPeriod()` and `deleteBlockReasonsForPeriods()` helpers
- Income show/index Inertia props include `deleteBlockReason` per period
- Delete income modal shows block alert, disables submit, and surfaces `errors.period` on failed delete
- Delete buttons hidden on show/index when delete is blocked

## Files touched

**Backend**

- `app/Services/Savings/FundBalanceService.php`
- `app/Http/Controllers/Savings/IncomePeriodController.php`

**Frontend**

- `resources/js/types/savings.ts`
- `resources/js/components/savings/delete-income-modal.tsx`
- `resources/js/pages/savings/income/show.tsx`
- `resources/js/pages/savings/income/index.tsx`

**Tests**

- `tests/Feature/Savings/IncomePeriodTest.php` (new scenarios + `createIncomePeriodFor` lookup fix)

## Deploy / verify

- No migrations
- `npm run dev` or `npm run build` if frontend not hot-reloading

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundSpendTest.php --filter="deleting income"
php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`)

### Happy path — delete allowed

1. Log in as a user with a savings plan and income recorded
2. Open **Income** → confirm **Delete** is visible on index and show when no blocking spends exist
3. Delete an income period → confirm redirect to index and success toast

### Blocked delete

1. Record spending in a fund bucket that exceeds what would remain if income were removed (no opening balance to cover)
2. Open **Income** → **Delete** should be hidden on index rows/cards and on show page
3. If delete is attempted via API, confirm validation error on `period`

### Opening balance fix

1. Set opening balance on a category (via plan edit) before or after income
2. Record spending covered by opening balance after income removal would still leave ≥ ₱0 remaining
3. Confirm delete is allowed

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Light and dark mode on delete modal alert
- [ ] Mobile width (~375px): income cards hide delete when blocked

## Suggested application commit

```
Summary: Block income delete when spends exceed remaining balance

Align assertCanRemovePeriod with opening-balance-aware remaining formula and expose deleteBlockReason on income UI so delete is disabled when confirmed draws would overdraw a fund bucket.
```

## Linear paste block

```
Title: Block income delete when spends exceed remaining

Description:
Income deletion now uses the same remaining formula as spend guards (opening balance + allocated − transfers + received − spent). Show and index pass deleteBlockReason; delete buttons and modal submit are disabled when blocked.

Comment / instructions:
Run npm run dev/build for frontend. Visual QA: record spend exceeding post-delete remaining → Delete hidden on Income. Suggested: php artisan test --compact tests/Feature/Savings/IncomePeriodTest.php and FundSpendTest filter deleting income.
```
