<?php

namespace Lokiwich\IranianBanks\Support;

class CardNumber
{
    /**
     * Normalize a card number or BIN: convert Persian/Arabic digits and strip non-digits.
     */
    public static function normalize(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $value = str_replace($persian, $latin, $value);
        $value = str_replace($arabic, $latin, $value);

        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /**
     * Extract the 6-digit BIN from a card number (or return null if fewer than 6 digits).
     */
    public static function bin(?string $value): ?string
    {
        $digits = self::normalize($value);

        if (strlen($digits) < 6) {
            return null;
        }

        return substr($digits, 0, 6);
    }

    /**
     * Among candidate prefixes, return the longest one that the card number starts with.
     * Prefixes shorter than 6 digits are ignored. Returns null when nothing matches.
     *
     * @param  list<string>  $prefixes
     */
    public static function bestMatchingPrefix(?string $value, array $prefixes): ?string
    {
        $digits = self::normalize($value);

        if (strlen($digits) < 6) {
            return null;
        }

        $best = null;
        $bestLen = 0;

        foreach ($prefixes as $prefix) {
            if ($prefix === '' || ! ctype_digit($prefix)) {
                continue;
            }

            $len = strlen($prefix);

            if ($len < 6 || $len <= $bestLen) {
                continue;
            }

            if (str_starts_with($digits, $prefix)) {
                $best = $prefix;
                $bestLen = $len;
            }
        }

        return $best;
    }

    /**
     * Validate a 16-digit card number using the Luhn algorithm.
     */
    public static function passesLuhn(?string $value): bool
    {
        $digits = self::normalize($value);

        if (strlen($digits) !== 16 || ! ctype_digit($digits)) {
            return false;
        }

        $sum = 0;
        $alt = false;

        for ($i = 15; $i >= 0; $i--) {
            $n = (int) $digits[$i];

            if ($alt) {
                $n *= 2;

                if ($n > 9) {
                    $n -= 9;
                }
            }

            $sum += $n;
            $alt = ! $alt;
        }

        return $sum % 10 === 0;
    }
}
