<?php

namespace Lokiwich\IranianBanks\Support;

/**
 * Iranian Sheba (IBAN) helpers.
 *
 * Structure (26 characters):
 *   IR + [2 check digits] + [3 bank code] + [19 account digits]
 */
class Iban
{
    /**
     * Normalize an IBAN: Persian/Arabic digits → Latin, strip spaces, uppercase.
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
        $value = strtoupper(preg_replace('/\s+/', '', $value) ?? '');

        return $value;
    }

    /**
     * Return the 24 national digits (without IR), or null if invalid length/format.
     */
    public static function digits(?string $value): ?string
    {
        $iban = self::normalize($value);

        if (str_starts_with($iban, 'IR')) {
            $iban = substr($iban, 2);
        }

        if (strlen($iban) !== 24 || ! ctype_digit($iban)) {
            return null;
        }

        return $iban;
    }

    /**
     * Full IR + 24 digits, or null.
     */
    public static function format(?string $value): ?string
    {
        $digits = self::digits($value);

        return $digits === null ? null : 'IR'.$digits;
    }

    /**
     * 3-digit Sheba bank code (positions 3–5 of the 24-digit body).
     */
    public static function bankCode(?string $value): ?string
    {
        $digits = self::digits($value);

        return $digits === null ? null : substr($digits, 2, 3);
    }

    /**
     * 19-digit account number embedded in the Sheba.
     */
    public static function accountNumber(?string $value): ?string
    {
        $digits = self::digits($value);

        return $digits === null ? null : substr($digits, 5);
    }

    /**
     * ISO 13616 mod-97 checksum validation for Iranian IBAN.
     */
    public static function passesMod97(?string $value): bool
    {
        $formatted = self::format($value);

        if ($formatted === null) {
            return false;
        }

        $rearranged = substr($formatted, 4).substr($formatted, 0, 4);
        $numeric = '';

        foreach (str_split($rearranged) as $char) {
            $numeric .= ctype_alpha($char)
                ? (string) (ord($char) - 55)
                : $char;
        }

        return bcmod($numeric, '97') === '1';
    }

    /**
     * Build a valid Iranian IBAN from bank code + 19-digit account.
     */
    public static function make(string $bankCode, string $accountNumber = '0000000000000000001'): string
    {
        $bankCode = str_pad(CardNumber::normalize($bankCode), 3, '0', STR_PAD_LEFT);
        $accountNumber = str_pad(CardNumber::normalize($accountNumber), 19, '0', STR_PAD_LEFT);

        if (strlen($bankCode) !== 3 || strlen($accountNumber) !== 19) {
            throw new \InvalidArgumentException('Bank code must be 3 digits and account 19 digits.');
        }

        $bban = $bankCode.$accountNumber;
        $numeric = $bban.'182700';
        $check = 98 - (int) bcmod($numeric, '97');

        return 'IR'.str_pad((string) $check, 2, '0', STR_PAD_LEFT).$bban;
    }
}
