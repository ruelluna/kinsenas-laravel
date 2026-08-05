# GoSave decouple + income "Received on" label — breakdown

**Date:** 2026-08-06

## Summary

GoTyme and GoSave are no longer added in one bundled wizard. Each GoTyme or GoSave account is added separately with a `GoTyme/…` or `GoSave/…` label (e.g. `GoSave/Mom`). The income entry form and index table now use **Received on** instead of **Period start** / **Date**.

## Changelog

- Removed bundled GoSave setup wizard; GoTyme uses the standard single-bank add flow
- GoTyme add modal: account type radio (GoTyme | GoSave) + name field → `account_label`
- Unlimited independent GoSave accounts (no 5-space cap, no `bank_account_group_id` for new rows)
- Removed `savings_spaces` feature from GoTyme catalog seed data
- GHL `gotyme-gosave-setup` tag fires when a GoSave-labeled account is added
- Income form label **Received on**; income index column **Received on**
- Legacy grouped GoTyme setups (existing `bank_account_group_id` rows) still display grouped

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| GoSave UX | GoTyme like any bank with `GoTyme/` / `GoSave/` labels | No separate institution row |
| Grouping for new banks | None | Each add is one standalone card |
| Legacy grouped setups | Retain grouping UI | No migration |
| Income rename | UI only | Backend stays `period_start` |

## Files touched

**Backend:** `philippine-bank-institutions.php`, `PhilippineBankSeeder.php`, `BankController` (web + API), `SaveBankRequest`, `BankGhlTagResolver`, `BankGhlTagService`, `BankPayloadMapper`; deleted `BankAccountSetupService`

**Frontend:** `add-bank-modal.tsx`, `gotyme-account-label.tsx` (new), deleted `gosave-space-setup.tsx`, `add-income-modal.tsx`, `income/index.tsx`, `banks/index.tsx`, `savings.ts`

**Tests:** `GoTymeBankSetupTest.php`, `PhilippineBankSeederTest.php`, `BankGhlTagTest.php`, `GoTymeBankTest.php` (browser)

## Deploy

```bash
php artisan db:seed --class=PhilippineBankSeeder
npm run dev
vendor/bin/pint --dirty
```

Re-seed clears GoTyme `features.savings_spaces` on existing environments.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Savings/GoTymeBankSetupTest.php
php artisan test --compact tests/Feature/Savings/BankGhlTagTest.php
php artisan test --compact tests/Feature/Seeders/PhilippineBankSeederTest.php
php artisan test --compact tests/Browser/GoTymeBankTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev` if frontend changed

### Happy path

1. Log in as a member with vault unlocked and subscription active
2. Open **Banks** → **Add bank**
3. Search **GoTyme Bank** → select it
4. Choose **GoSave**, enter **Mom** → **Add bank**
5. Confirm card shows **GoTyme Bank — GoSave/Mom** (standalone, not nested)
6. Add another GoSave (e.g. Partner) — no limit error
7. Open **Income** → **Add income** → confirm date field reads **Received on**

### Checks

- [ ] No console errors
- [ ] GoTyme main account (`GoTyme/Main`) adds separately from GoSave accounts
- [ ] Income index table column header is **Received on**

## Suggested application commit

```
Summary: Decouple GoSave from bundled GoTyme setup and rename income date label

GoTyme and GoSave are added one account at a time with GoTyme/ or GoSave/ labels.
Income entry uses "Received on" to match the mobile API receivedOn field.
```
