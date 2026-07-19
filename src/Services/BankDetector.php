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
     * When multiple brands share a BIN family (e.g. Saman / Blu), the longest
     * matching card prefix wins (8-digit Blu over 6-digit Saman).
     */
    public function detect(?string $cardNumber): ?Bank
    {
        $digits = CardNumber::normalize($cardNumber);

        if (strlen($digits) < 6) {
            return null;
        }

        $bestBank = null;
        $bestLen = 0;

        $banks = Bank::query()
            ->where('is_active', true)
            ->get();

        foreach ($banks as $bank) {
            $prefix = CardNumber::bestMatchingPrefix($digits, $bank->card_prefixes ?? []);

            if ($prefix === null) {
                continue;
            }

            $len = strlen($prefix);

            if ($len > $bestLen) {
                $bestBank = $bank;
                $bestLen = $len;
            }
        }

        return $bestBank;
    }

    /**
     * Detect an Iranian bank from a Sheba / IBAN using the 3-digit bank code.
     *
     * When multiple brands share a Sheba code (e.g. Saman / Blu), the parent
     * bank is preferred over subsidiary brand rows.
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
