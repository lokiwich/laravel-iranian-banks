<?php

namespace Lokiwich\IranianBanks\Facades;

use Illuminate\Support\Facades\Facade;
use Lokiwich\IranianBanks\Models\Bank;
use Lokiwich\IranianBanks\Services\BankDetector;

/**
 * @method static Bank|null detect(?string $cardNumber)
 * @method static Bank|null detectFromIban(?string $iban)
 *
 * @see BankDetector
 */
class IranianBanks extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BankDetector::class;
    }
}
