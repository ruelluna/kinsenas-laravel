# Android shared UI implementation

**Date:** 2026-08-04

## Summary

Shipped the foundation for a cross-platform Kinsenas client: pnpm/npm workspaces with `@kinsenas/shared`, `@kinsenas/ui` (Tamagui), `@kinsenas/api-client`, Laravel Sanctum JSON API (`/api/v1/*`), token-scoped vault storage, and an Expo Android app under `apps/mobile` sharing the same UI kit as the web app.

## Changelog

- Monorepo: `packages/shared`, `packages/ui`, `packages/api-client`, `apps/mobile`
- Tamagui theme aligned to Kinsenas brand tokens (`#0D7377` primary)
- Laravel Sanctum + `routes/api.php` v1 (auth, vault, teams, dashboard, savings, billing, admin)
- Vault DEKs stored in cache keyed by Sanctum token ID (24h TTL) for mobile; web still uses session store
- Expo app: login, vault unlock, dashboard, income/spending/transfers, billing, admin lists
- Web: `KinsenasProvider` in `app.tsx`, `format-money` re-exported from `@kinsenas/shared`, Vite alias for `react-native-web`
- EAS Build config (`eas.json`) and Play Store submit profile

## Files touched

### Backend
- `routes/api.php`, `bootstrap/app.php`, `app/Models/User.php`
- `app/Services/Vault/*`, `app/Contracts/Vault/VaultKeyStore.php`
- `app/Http/Middleware/BindVaultKeyStore.php`, `EnsureApiTeamScope.php`, `EnsureVaultUnlocked.php`
- `app/Http/Controllers/Api/V1/**`, `app/Http/Resources/*`, `app/Services/Api/SharedPropsService.php`

### Frontend / monorepo
- `pnpm-workspace.yaml`, `package.json`, `vite.config.ts`, `tsconfig.json`
- `packages/*`, `apps/mobile/**`, `resources/js/app.tsx`, `resources/js/lib/format-money.ts`

### Tests
- `tests/Feature/Api/V1/AuthTest.php`, `VaultTest.php`, `DashboardTest.php`, `AdminTest.php`

## Deploy / migration steps

```bash
php artisan migrate
npm install
cd apps/mobile && npm install
npm run build
```

Configure `EXPO_PUBLIC_API_URL` for mobile builds. Add PNG assets under `apps/mobile/assets/` before EAS build (see `apps/mobile/README.md`).

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Api/V1/
vendor/bin/pint --dirty
npm run types:check
cd apps/mobile && npx expo run:android
```

## Visual QA (manual)

**Web:** http://financial-literacy.test — confirm app loads with `KinsenasProvider` (no console errors).

**Mobile:** Expo dev client → login → vault unlock → dashboard fund balances → savings lists → admin (platform admin user).

## Suggested commit

```
Summary: Add shared Tamagui UI monorepo, Sanctum API, and Expo Android app

Introduces @kinsenas/ui/shared/api-client packages, Laravel /api/v1 JSON endpoints with token-scoped vault storage, and an Expo Android shell reusing the same components as web.
```

## Linear paste block

```
Title: Android shared UI + Sanctum API foundation

Description:
Monorepo with Tamagui (@kinsenas/ui), shared types/formatting, Sanctum API for mobile, token-scoped vault unlock, and Expo Android app with member + admin read screens.

Comment / instructions:
Run php artisan migrate. npm install at root and apps/mobile. Set EXPO_PUBLIC_API_URL. Add mobile assets before EAS build. Suggested: php artisan test --compact tests/Feature/Api/V1/
```
