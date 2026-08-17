# Finance Activity Score — breakdown

**Date:** 2026-08-17

## Summary

Shipped a hybrid **Finance Activity Score (0–100%)** cached on `users` and `teams`, with tier labels and admin filters on **Users** and **Subscribers** lists. Score combines setup milestones (50%), recency (25%), and frequency (25%). Complements existing GHL activation tags — does not replace them.

## Changelog

- New `FinanceActivityScoreService` computes setup / recency / frequency and tier (`inactive`, `partial`, `active`, `activated`)
- Cached columns on `users` and `teams`; refreshed via model observers and daily `users:refresh-finance-activity-scores`
- Admin **Users** and **Subscribers** index: activity tier filter, min score filter, score + tier display
- Income locked → **Activated** tier (matches GHL `activated-user` semantics)

## Decisions

| Topic | Choice | Implication |
| ----- | --- | ----- |
| GHL activation tags | Retain | Score is complementary read-only admin metric |
| Income locked field | `income_periods.is_locked` | Not `savings_plans` |
| Vault recency | Exclude vault timestamps | Session unlocks are not finance actions |

## Files touched

### Backend
- `app/Enums/FinanceActivityTier.php`
- `app/Data/FinanceActivitySnapshot.php`
- `app/Services/Users/FinanceActivityScoreService.php`
- `app/Observers/FinanceActivityScoreObserver.php`
- `app/Console/Commands/Users/RefreshFinanceActivityScoresCommand.php`
- `app/Providers/AppServiceProvider.php`
- `app/Http/Controllers/Admin/AdminPlatformUserController.php`
- `app/Http/Controllers/Admin/AdminSubscriberController.php`
- `app/Models/User.php`, `Team.php`, `FundSpend.php`, `FundTransfer.php`
- `database/migrations/2026_08_17_013508_add_finance_activity_columns_to_users_and_teams_table.php`
- `routes/console.php`

### Frontend
- `resources/js/types/billing.ts`
- `resources/js/pages/admin/platform-users/index.tsx`
- `resources/js/pages/admin/subscribers/index.tsx`

### Tests
- `tests/Unit/Services/Users/FinanceActivityScoreServiceTest.php`
- `tests/Feature/Admin/FinanceActivityAdminTest.php`

## Deploy steps

1. `php artisan migrate`
2. `php artisan users:refresh-finance-activity-scores` (backfill existing rows)
3. `npm run dev` or `npm run build` if frontend not hot-reloading

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Unit/Services/Users/FinanceActivityScoreServiceTest.php
php artisan test --compact tests/Feature/Admin/FinanceActivityAdminTest.php
php artisan test --compact tests/Feature/Admin/PlatformUserAdminTest.php
php artisan test --compact tests/Feature/Admin/SubscriberAdminTest.php
vendor/bin/pint --dirty
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** migrate, backfill command, `npm run dev`

### Happy path

1. Log in as platform admin
2. Open **Users** → confirm **Finance activity: NN% · Tier** on each row
3. Filter **Activity tier** → **Inactive** → list narrows
4. Set **Min score** to `60` → only active-enough users remain
5. Open **Subscribers** → same filters and score column on team rows

### Checks

- [ ] No console errors
- [ ] Filter state persists in query string after submit
- [ ] Tier labels readable (Inactive / Partial / Active / Activated)

## Suggested application commit

```
Summary: Add finance activity score for admin user filtering

Hybrid 0–100% score (setup + recency + frequency) cached on users and teams.
Admin Users and Subscribers lists gain tier/min-score filters and score display.
Run migrate and users:refresh-finance-activity-scores after deploy.
```
