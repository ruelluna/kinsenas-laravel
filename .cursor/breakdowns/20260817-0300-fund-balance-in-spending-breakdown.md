# Fund balance in Record spending

**Date:** 2026-08-17

## Summary

Fund bucket dropdown options in **Record spending** and **Edit spending** modals now show each fund’s current remaining balance (e.g. `Everyday Fund — ₱35,000.00 remaining`). Balances come from the existing `fundBalances` prop — no backend changes.

## Changelog

- Fund bucket `<select>` options include formatted remaining balance in Record and Edit spending modals
- Vault locked: option shows fund name only (no awkward `— remaining` copy)
- **After this spend…** projection below amount unchanged
- Browser test asserts selected option labels via `assertScript` (native selects are not visible to `assertSee`)

## Decisions

| Topic | Choice | Implication |
| ----- | ------ | ----------- |
| Placement | Balance in dropdown options | User chose over hint below select |
| Scope | Record + Edit modals | Both updated for parity |

## Files touched

**Frontend**

- `resources/js/lib/fund-bucket-option-label.ts` (new)
- `resources/js/components/savings/add-spending-modal.tsx`
- `resources/js/components/savings/edit-spending-modal.tsx`
- `resources/js/pages/savings/spending/index.tsx` (`data-test="edit-spending-button"`)

**Tests**

- `tests/Browser/SpendingReimbursementTest.php`

## Deploy / verify

- `npm run build` (or `npm run dev`) after frontend deploy

## Suggested tests (run manually)

```bash
npm run build
php artisan test --compact tests/Browser/SpendingReimbursementTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` or `npm run build`

### Happy path

1. Log in with a team that has income and unlocked vault
2. Open **Spending** → **Add spending** (or Dashboard quick action)
3. Confirm **Fund bucket** dropdown shows `Name — ₱X,XXX.XX remaining` per fund
4. Change fund bucket → selected label updates
5. Enter an amount → **After this spend…** still appears
6. With **Allow editing and deleting recorded spending** enabled, open **Edit** on a spend → same balance labels in dropdown

### Checks

- [ ] No console errors
- [ ] Vault locked: options show names only

## Suggested commit

```
Summary: Show fund remaining balance in spending modal dropdowns

Fund bucket options in Record and Edit spending modals now include
formatted remaining balance so users see available funds before entering
an amount. Browser test covers both modals via selected option text.
```

## Implementation summary

- Record/Edit spending fund selects show `Everyday Fund — ₱35,000.00 remaining` style labels
- Shared `fundBucketOptionLabel` helper; vault-locked shows name only
- Browser test extended for Record + Edit modal option labels
