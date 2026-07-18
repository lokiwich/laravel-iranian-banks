# Laravel Iranian Banks

Iranian Shetab banks for Laravel — names (FA/EN), SVG icons, card BIN prefixes, Sheba bank codes, and bank detection from card numbers or IBAN.

## Features

- Directory of Shetab banks with Persian & English names
- Multiple card BIN prefixes per bank stored as JSON
- Sheba (`iban_code`) per bank + mod-97 IBAN helpers
- Detect bank from a card number / BIN (`6+` digits) or Sheba
- Persian / Arabic digit normalization
- Optional Luhn validation helper
- Colored & monochrome SVG logos (vendored from [snapp-store/iranian-banks-react-icons](https://github.com/snapp-store/iranian-banks-react-icons), MIT)
- Blade component for bank logos
- `php artisan iranian-banks:install`

## Requirements

- PHP 8.2+
- Laravel 10–13

## Installation

```bash
composer require lokiwich/laravel-iranian-banks
php artisan iranian-banks:install
```

Or publish pieces manually:

```bash
php artisan vendor:publish --tag=iranian-banks-config
php artisan vendor:publish --tag=iranian-banks-migrations
php artisan migrate
php artisan db:seed --class="Lokiwich\\IranianBanks\\Database\\Seeders\\IranianBanksSeeder"
```

### Local path development

```json
{
  "repositories": [
    { "type": "path", "url": "packages/laravel-iranian-banks", "options": { "symlink": true } }
  ],
  "require": {
    "lokiwich/laravel-iranian-banks": "*"
  }
}
```

## Usage

```php
use Lokiwich\IranianBanks\Facades\IranianBanks;
use Lokiwich\IranianBanks\Models\Bank;
use Lokiwich\IranianBanks\Support\CardNumber;
use Lokiwich\IranianBanks\Support\Iban;

$bank = IranianBanks::detect('6037697512345678');
// or
$bank = Bank::findByCardNumber('۶۰۳۷۶۹');

$iban = Iban::make('012', '1234567890123456789'); // valid IR… Sheba
$bank = IranianBanks::detectFromIban($iban);       // Bank Mellat
Iban::passesMod97($iban);                          // true
Iban::bankCode($iban);                             // '012'

$bank?->name_fa;          // بانک صادرات ایران
$bank?->name_en;          // Bank Saderat Iran
$bank?->card_prefixes;    // ['603769', '903769']
$bank?->iban_code;        // '019'
$bank?->displayName();    // locale-aware
$bank?->iconSvg('color'); // SVG markup

CardNumber::normalize('۶۰۳۷-۶۹'); // '603769'
CardNumber::bin('6037697512345678'); // '603769'
CardNumber::passesLuhn('6037697512345678'); // bool
```

### Blade logo

```blade
<x-iranian-banks::bank-logo :bank="$bank" variant="color" :width="40" :height="40" />
```

## Publishing to Packagist

1. Push `packages/laravel-iranian-banks` to its own GitHub repository (e.g. `lokiwich/laravel-iranian-banks`).
2. Create a release / tag (`v1.0.0`).
3. Submit the package on [packagist.org](https://packagist.org) and enable GitHub Auto-Update.

## Icon attribution

SVG bank logos are adapted from [@snapp-store/iranian-banks-react-icons](https://github.com/snapp-store/iranian-banks-react-icons) (MIT © Snapp Store).

## License

MIT © Lokiwich
