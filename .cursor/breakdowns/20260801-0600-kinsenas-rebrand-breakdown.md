# Kinsenas rebrand — Implementation Breakdown

**Date:** 2026-08-01

## Summary

Rebranded the application from FutureSave / Financial Literacy to **Kinsenas**. Centralized the display name through `APP_NAME` and shared Inertia `name` prop; removed hardcoded brand strings from UI copy and HTTP User-Agent.

## Changelog

- `APP_NAME` and defaults set to **Kinsenas** (`.env`, `.env.example`, `config/app.php`, Vite title fallback)
- Welcome page, sidebar logo, auth panel, and browser tab titles use **Kinsenas** via shared `name` prop
- Transfer modal bank reminder uses dynamic app name instead of hardcoded FutureSave
- Bank logo fetch User-Agent uses `config('app.name')` and `config('app.url')`
- Cursor agent rules updated to reference Kinsenas
- README retitled to Kinsenas

## Files touched

**Config / env**

- `.env`, `.env.example`
- `config/app.php`

**Backend**

- `app/Services/Savings/BankInstitutionLogoService.php`

**Frontend**

- `resources/js/app.tsx`
- `resources/js/pages/welcome.tsx`
- `resources/js/components/savings/add-transfer-modal.tsx`

**Docs / rules**

- `README.md`
- `.cursor/rules/laravel-boost.mdc`
- `.cursor/rules/money-formatting.mdc`
- `.cursor/rules/plan-implementation.mdc`
- `.cursor/rules/documentation.mdc`

## Deploy / verify

- Restart `npm run dev` (or run `npm run build`) so `VITE_APP_NAME` picks up the new `.env` value
- No migrations

## Suggested tests (run manually)

```bash
# Optional — confirm shared app name in Inertia props

php artisan test --compact --filter=Welcome
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** Restart Vite dev server after `.env` change

### Happy path

1. Open `/` — header and hero copy show **Kinsenas**
2. Log in — sidebar logo label reads **Kinsenas**
3. Open auth pages (login/register) — split panel shows **Kinsenas**
4. Browser tab titles use `Page title - Kinsenas`
5. **Savings → Transfers → Add transfer** (bank-to-bank) — reminder dialog says "Before confirming in Kinsenas…"

### Checks

- [ ] No console errors
- [ ] Mail from-name (if testing email) shows Kinsenas

## Suggested commit

```
Summary: Rebrand application to Kinsenas

Centralize display name through APP_NAME and shared Inertia props.
Replace hardcoded FutureSave / Financial Literacy strings in UI and services.
```
