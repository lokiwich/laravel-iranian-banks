# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-07-16

### Added

- Sheba `iban_code` column and seed data for Shetab banks
- `IranianBanks::detectFromIban()` / `Bank::findByIban()` / `Iban` helpers (mod-97, bank code extraction)
- Validation messages for checksum and unknown bank code

## [1.0.0] - 2026-07-16

### Added

- Initial release with Shetab bank directory (names, card BIN prefixes, SVG icons)
- `Bank::findByCardNumber()` / `IranianBanks::detect()` API
- Persian/Arabic digit normalization and Luhn helpers
- Blade `<x-iranian-banks::bank-logo>` component
- `php artisan iranian-banks:install` command
- Orchestra Testbench test suite
