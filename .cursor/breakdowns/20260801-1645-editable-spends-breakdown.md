# Editable spends with plan setting

**Date:** 2026-08-01

## Summary

Spending entries can be edited and deleted when the savings plan has **Allow editing and deleting recorded spending** enabled. The toggle lives on the Savings Plan page alongside **Share plan with team members**. Balance validation runs on update (same rules as create, accounting for the existing amount).

## Changelog

- New `allow_editing_spends` boolean on `savings_plans` (default off)
- Plan page checkbox to enable/disable spend editing for the team
- Spending page shows **Edit** and **Delete** on recent activity when enabled
- Edit modal: amount, fund, description, date, recipient, receipt
- PUT/DELETE routes for fund spends with balance checks on update
- Plan guidance table documents the new setting

## Files touched

### Backend

- `database/migrations/2026_07_31_204750_add_allow_editing_spends_to_savings_plans_table.php`
- `app/Models/SavingsPlan.php`
- `database/factories/SavingsPlanFactory.php`
- `app/Services/Savings/SavingsPlanService.php`
- `app/Services/Savings/FundBalanceService.php`
- `app/Services/Savings/FundSpendService.php`
- `app/Http/Controllers/Savings/FundSpendController.php`
- `app/Http/Controllers/Savings/SavingsPlanController.php`
- `app/Http/Requests/Savings/SaveSavingsPlanRequest.php`
- `app/Http/Requests/Savings/UpdateFundSpendRequest.php`
- `routes/savings.php`

### Frontend

- `resources/js/pages/savings/spending/index.tsx`
- `resources/js/pages/savings/plan.tsx`
- `resources/js/components/savings/edit-spending-modal.tsx`
- `resources/js/components/savings/receipt-upload-field.tsx`
- `resources/js/components/savings/plan-guidance-panels.tsx`
- `resources/js/types/savings.ts`

### Tests

- `tests/Feature/Savings/FundSpendTest.php`

## Deploy steps

```bash
php artisan migrate
npm run dev   # or npm run build if frontend changed
```

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/FundSpendTest.php

vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend changed; `php artisan migrate`

### Enable setting

1. Log in as a user with a savings plan and locked income
2. Open **Savings plan** in the sidebar
3. Check **Allow editing and deleting recorded spending**
4. Click **Save plan**

### Edit / delete

1. Open **Spending** → record a quick spend
2. Confirm **Edit** and **Delete** buttons appear on the row
3. Click **Edit** → change amount/description → **Save changes**
4. Confirm fund balance and row update
5. Click **Delete** on another row → confirm balance restores

### Setting off

1. Uncheck the plan setting and save
2. Open **Spending** → confirm Edit/Delete buttons are hidden
3. Attempting update via API should return validation error

### Checks

- [ ] No console errors
- [ ] Light and dark mode on edit modal
- [ ] Receipt upload/replace/remove in edit modal
- [ ] Pending spends still show Confirm alongside Edit when enabled

## Suggested application commit

```
Summary: Add configurable editable spends per savings plan

Introduce allow_editing_spends on savings plans so teams can opt in to
editing and deleting recorded spending. Update and delete routes validate
balances; UI shows Edit/Delete on the Spending page when enabled.
```
