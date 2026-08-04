# Income todo complete confirmation — breakdown

**Date:** 2026-08-04

## Summary

Marking an income distribution todo complete now opens a confirmation dialog reminding members that Kinsenas cannot verify bank transfers and that marking complete without moving money makes balances unrealistic.

## Changelog

- New **Confirm bank transfer?** dialog before POST to complete todo
- **Move to your banks** intro copy clarifies honest-checklist behavior
- **Mark complete** opens modal; **I transferred this — mark complete** submits

## Files touched

**Frontend**

- `resources/js/components/savings/confirm-income-distribution-todo-modal.tsx` (new)
- `resources/js/components/savings/income-distribution-todos.tsx`

## Deploy / verify

- No migrations or backend changes
- `npm run dev` or `npm run build` for frontend

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/IncomeDistributionTodoTest.php
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` (or `npm run build`)

### Happy path

1. Log in with a savings plan and at least one income period
2. Open **Income** → income period detail
3. In **Move to your banks**, click **Mark complete** on a pending row
4. Confirm dialog shows amount, fund bucket, and bank (if assigned)
5. Click **Cancel** → dialog closes, row stays pending
6. Click **Mark complete** again → **I transferred this — mark complete**
7. Confirm success toast and row shows confirmed state

### No bank assigned

1. Use a fund bucket with no bank on the plan
2. Open confirm dialog → copy mentions assigning a bank; link to **Plan** works

### Checks

- [ ] No console errors (DevTools → Console)
- [ ] Light and dark mode on dialog
- [ ] Mobile width (~375px): dialog scrolls and buttons usable

### Regression

- [ ] Income index progress badges still update after completing a todo

## Suggested application commit

```
Summary: Confirm before marking income bank transfers complete

Add a reminder dialog before completing distribution todos so members acknowledge Kinsenas cannot verify real bank transfers and balances stay realistic.
```

## Linear paste block

```
Title: Confirm before marking income bank transfers complete

Description:
Income distribution checklist now shows a confirmation dialog before mark complete. Copy explains Kinsenas cannot verify bank activity and that marking without transferring makes balances unrealistic.

Comment / instructions:
npm run dev/build. Visual QA: Income detail → Mark complete → confirm/cancel flow. Suggested: php artisan test --compact tests/Feature/Savings/IncomeDistributionTodoTest.php
```
