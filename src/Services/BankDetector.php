<?php

namespace Lokiwich\IranianBanks\Services;

use Lokiwich\IranianBanks\Models\Bank;
use Lokiwich\IranianBanks\Support\CardNumber;
use Lokiwich\IranianBanks\Support\Iban;

class BankDetector
{
    /**
     * Detect an Iranian bank from a card number or BIN (6+ digits).
     *
     * When multiple brands share a BIN (e.g. Saman / Blu), the parent bank
     * is preferred over subsidiary brand rows.
     */
    public function detect(?string $cardNumber): ?Bank
    {
        $bin = CardNumber::bin($cardNumber);

        if ($bin === null) {
            return null;
        }

        return Bank::query()
            ->where('is_active', true)
            ->whereJsonContains('card_prefixes', $bin)
            ->orderByRaw("CASE WHEN slug = 'blu' THEN 1 ELSE 0 END")
            ->first();
    }

    /**
     * Detect an Iranian bank from a Sheba / IBAN using the 3-digit bank code.
     */
    public function detectFromIban(?string $iban): ?Bank
    {
        $bankCode = Iban::bankCode($iban);

        if ($bankCode === null) {
            return null;
        }

        return Bank::query()
            ->where('is_active', true)
            ->whereJsonContains('iban_codes', $bankCode)
            ->orderByRaw("CASE WHEN slug = 'blu' THEN 1 ELSE 0 END")
            ->first();
    }
}
