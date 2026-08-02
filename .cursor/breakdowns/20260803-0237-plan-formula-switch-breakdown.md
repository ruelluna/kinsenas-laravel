# Plan formula switch (before income)

**Date:** 2026-08-03

## Summary

Members who picked a savings formula but have not entered income yet can discard the draft plan and return to the formula chooser. After the first income entry, switching formulas is blocked (403).

## Changelog

- **Savings Plan** — "Choose a different formula" button on the plan editor when no income exists; confirms, then removes the plan and shows the template chooser again
- **Guidance copy** — "Before you choose" note mentions going back before income is saved
- **API** — `DELETE /{team}/savings/plan` (`savings.plan.destroy`) for plan creator only, before income

## Files touched

### Backend

- `app/Services/Savings/SavingsPlanService.php` — `discardDraft()`
- `app/Http/Controllers/Savings/SavingsPlanController.php` — `destroy()`
- `app/Policies/SavingsPlanPolicy.php` — `delete()`
- `routes/savings.php` — delete route
- `database/seeders/SavingsPlanPageGuidanceSeeder.php` — updated `before_choose_note`

### Frontend

- `resources/js/pages/savings/plan.tsx` — button + confirmation dialog

### Tests

- `tests/Feature/Savings/SavingsPlanTest.php` — discard happy path + forbidden after income

## Deploy / verify

- No migration
- `php artisan wayfinder:generate --no-interaction` — new delete route (or confirm Vite Wayfinder regen on dev)
- `npm run dev` or `npm run build` — frontend changed

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/SavingsPlanTest.php

vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test
**Prereqs:** `npm run dev` if frontend not hot-reloading

### Happy path

1. Log in as a user with vault unlocked and no savings plan
2. Open **Savings Plan** in the sidebar
3. Pick a formula (e.g. TRC) — plan editor loads
4. Click **Choose a different formula** → confirm **Choose another formula**
5. Confirm template chooser returns and toast says plan was removed
6. Pick a different formula — new plan creates successfully

### Checks

- [ ] Button hidden after first income entry
- [ ] No console errors
- [ ] Light and dark mode on dialog

### Regression

- [ ] Save plan still works before income
- [ ] Income lock rules unchanged after first income

## Suggested application commit

```
Summary: Allow switching savings formula before first income

Members can discard an unsaved plan and return to the formula chooser until
they record income. After income exists, delete is forbidden to protect
historical breakdowns.
```

## Implementation summary (paste)

## Switch savings formula before income

- Plan editor shows **Choose a different formula** when no income has been entered
- Confirming removes the draft plan and returns to the template chooser
- After first income, switching is blocked (same lock as percentage edits)
- "Before you choose" guidance mentions the go-back option

Visual QA: see breakdown Visual QA section.
