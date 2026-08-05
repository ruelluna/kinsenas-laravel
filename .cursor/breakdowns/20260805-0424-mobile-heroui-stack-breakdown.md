# Mobile HeroUI Native + Uniwind stack

**Date:** 2026-08-05

## Summary

Migrated `apps/mobile` from Tamagui/`@kinsenas/ui` to the target stack: Expo SDK 56, Expo Router, HeroUI Native, Uniwind, React Hook Form, Zod, and TanStack Query. Web Inertia app continues using `@kinsenas/ui` (Tamagui) unchanged.

## Changelog

- Upgraded mobile app to Expo SDK 56 (React 19.2, React Native 0.85)
- Added HeroUI Native + Uniwind (`global.css`, `metro.config.js`, provider wiring)
- Removed `tamagui` and `@kinsenas/ui` from mobile dependencies
- Rebuilt `MobileShell` with HeroUI BottomSheet nav menu and HeroUI layout primitives
- Added `MobileDataList` for compact list screens (income, spending, transfers, admin)
- Login and vault unlock use React Hook Form + Zod validation with HeroUI `TextField`/`Input`
- All 12 route screens use HeroUI Native components only

## Files touched

### Mobile app

- `apps/mobile/package.json`, `package-lock.json`, `app.json`, `tsconfig.json`, `expo-env.d.ts`
- `apps/mobile/global.css`, `metro.config.js`
- `apps/mobile/app/_layout.tsx`
- `apps/mobile/app/(auth)/login.tsx`, `vault-unlock.tsx`
- `apps/mobile/app/(app)/dashboard.tsx`, `billing.tsx`
- `apps/mobile/app/(app)/savings/income.tsx`, `spending.tsx`, `transfers.tsx`
- `apps/mobile/app/(app)/admin/*.tsx` (4 screens)
- `apps/mobile/components/mobile-shell.tsx`, `mobile-data-list.tsx`, `loading-state.tsx`
- `apps/mobile/lib/schemas/login-schema.ts`, `vault-unlock-schema.ts`
- `apps/mobile/README.md`

### Unchanged (web)

- `packages/ui/**` — Tamagui kit for Inertia web

## Deploy / verify

```bash
cd apps/mobile
npm install
npx expo start
```

First Metro start generates `uniwind-types.d.ts`.

```bash
cd apps/mobile && npm run types:check
cd apps/mobile && npx expo run:android
npm run build   # repo root — confirm web still builds
```

Set `EXPO_PUBLIC_API_URL` for API calls (see `apps/mobile/README.md`).

## Suggested tests (run manually)

```bash
cd apps/mobile && npm run types:check
cd apps/mobile && npx expo run:android
php artisan test --compact tests/Feature/Api/V1/
```

## Visual QA (manual)

**URL:** Expo dev client / Android emulator  
**Prereqs:** `cd apps/mobile && npx expo start`

### Happy path

1. Open app → **Login** screen (HeroUI Card + fields)
2. Submit empty form → Zod errors on email/password
3. Sign in → vault unlock (if locked) → **Dashboard** with summary cards
4. Tap **☰** → menu BottomSheet → navigate Income, Spending, Transfers, Billing
5. Platform admin: menu shows admin destinations

### Checks

- [ ] No redbox / Metro errors
- [ ] HeroUI buttons, cards, inputs render correctly
- [ ] Light/dark follows system (`userInterfaceStyle: automatic`)
- [ ] Money values use `formatMoney` (₱ prefix, commas)

### Regression

- [ ] Web `npm run build` succeeds
- [ ] `@kinsenas/ui` still used by Inertia (`resources/js/app.tsx`)

## Suggested application commit

```
Summary: Migrate mobile app to HeroUI Native and Uniwind stack

Replace Tamagui/@kinsenas/ui on apps/mobile with HeroUI Native, Uniwind,
React Hook Form, and Zod on Expo SDK 56. Web keeps Tamagui via @kinsenas/ui.
```

## Linear paste block

```
Title: Migrate mobile app to HeroUI Native and Uniwind stack

Description:
apps/mobile now uses Expo 56, HeroUI Native, Uniwind, RHF, Zod, and TanStack Query.
Tamagui and @kinsenas/ui removed from mobile; web Inertia unchanged.

Comment / instructions:
cd apps/mobile && npm install && npx expo start. Visual QA: login validation,
dashboard, menu nav, admin screens. types:check passes. Web: npm run build at root.
```
