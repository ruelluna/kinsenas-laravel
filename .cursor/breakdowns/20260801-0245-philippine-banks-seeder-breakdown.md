# Philippine Banks Catalog Seeder

**Date:** 2026-08-01

## Summary

Added a platform-wide `bank_institutions` catalog seeded with 34 Philippine banks and e-wallets. Logos are downloaded from Wikimedia Commons (primary) or Google favicon service (fallback) during seeding and stored on the public disk.

## Changelog

- New `bank_institutions` table with UUID PK, slug, type (`bank` / `e_wallet`), and `logo_path`
- `PhilippineBankSeeder` seeds 26 banks and 8 e-wallets idempotently
- `BankInstitutionLogoService` downloads and caches logos under `storage/app/public/bank-institutions/`
- Registered seeder in `DatabaseSeeder`
- Added `BankFactory` (was referenced but missing)

## Files touched

**Backend**

- `app/Enums/BankInstitutionType.php`
- `app/Models/BankInstitution.php`
- `app/Services/Savings/BankInstitutionLogoService.php`
- `database/migrations/2026_07_31_183925_create_bank_institutions_table.php`
- `database/migrations/2026_07_31_184653_rebuild_bank_institutions_table.php`
- `database/data/philippine-bank-institutions.php`
- `database/seeders/PhilippineBankSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `database/factories/BankInstitutionFactory.php`
- `database/factories/BankFactory.php`

**Tests**

- `tests/Feature/Seeders/PhilippineBankSeederTest.php`

## Deploy steps

```bash
php artisan migrate
php artisan storage:link   # if not already linked
php artisan db:seed --class=PhilippineBankSeeder
```

First seed requires outbound HTTP (~45–90s with rate limiting). Re-runs skip existing logo files.

## Suggested tests (run manually)

```bash
php artisan test --compact tests/Feature/Seeders/PhilippineBankSeederTest.php
vendor/bin/pint --dirty
```

## Visual QA

N/A — no UI changes in this pass. Future picker UI can consume `BankInstitution` with `logoUrl`.

## Suggested commit

```
Summary: Add Philippine bank and e-wallet institution catalog seeder

Seeds a platform-wide bank_institutions table with major PH banks and e-wallets,
downloading logos from Wikimedia Commons or favicon fallback into public storage.
```
