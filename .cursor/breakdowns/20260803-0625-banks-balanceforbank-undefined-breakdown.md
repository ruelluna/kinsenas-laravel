# Fix Banks page `balanceForBank` ReferenceError

**Date:** 2026-08-03 06:25

## Summary

Visiting **Banks** crashed with `Uncaught ReferenceError: balanceForBank is not defined`. An ESLint cleanup had moved the helper inside `useMemo` while the list render still called it in JSX.

## Changelog

- **Banks** page renders again without a console ReferenceError
- Bank cards and grouped GoSave spaces still show per-account / combined balances

## Files touched

### Frontend

- `resources/js/pages/savings/banks/index.tsx` — restore component-scoped `balanceForBank` for JSX + grouping

## Deploy / verify

- `npm run build` (or `npm run dev`) so production/beta assets pick up the fix
- Manual check: http://financial-literacy.test → **Banks** with at least one bank (and ideally a GoSave group)

## Suggested tests (run manually)

```bash
# Feature tests (existing bank coverage if present)
php artisan test --compact --filter=Bank

# Frontend lint for this file
npx eslint resources/js/pages/savings/banks/index.tsx
```

No new browser test added; smoke Visual QA is enough for this regression.

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`) if frontend changed

### Happy path

1. Log in as a member with a team
2. Open **Banks**
3. Confirm the page loads (no blank screen / console `balanceForBank` error)
4. With banks present, confirm balances and fund-bucket breakdowns still show
5. If a GoSave group exists, confirm combined balance and nested accounts render

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Sidebar active state matches **Banks**
- [ ] Light and dark mode
- [ ] Mobile width (~375px): layout usable

### Regression

- [ ] **Add bank** modal still opens
- [ ] Empty state still shows when the team has no banks

## Suggested application commit

```
Summary: Fix Banks page crash from undefined balanceForBank

Restore the helper at component scope so list rendering can look up
balances after the ESLint useMemo refactor.
```

## Linear paste block

```
Title: Fix Banks page balanceForBank ReferenceError

Description:
Visiting Banks threw Uncaught ReferenceError: balanceForBank is not defined. The helper was scoped inside useMemo during an ESLint cleanup while JSX still called it. Restored component-scoped lookup.

Comment / instructions:
Redeploy / rebuild frontend assets (`npm run build`). Visual QA: open Banks with banks present; confirm no console error and balances still show. Suggested: `npx eslint resources/js/pages/savings/banks/index.tsx`.
```
