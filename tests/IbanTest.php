<?php

namespace Lokiwich\IranianBanks\Tests;

use Lokiwich\IranianBanks\Support\Iban;
use PHPUnit\Framework\Attributes\Test;

class IbanTest extends TestCase
{
    #[Test]
    public function it_builds_and_validates_mod97_checksum(): void
    {
        $iban = Iban::make('012', '1234567890123456789');

        $this->assertSame(26, strlen($iban));
        $this->assertTrue(str_starts_with($iban, 'IR'));
        $this->assertTrue(Iban::passesMod97($iban));
        $this->assertTrue(Iban::passesMod97(Iban::digits($iban)));
    }

    #[Test]
    public function it_rejects_invalid_checksum(): void
    {
        $this->assertFalse(Iban::passesMod97('000000000000000000000000'));
        $this->assertFalse(Iban::passesMod97('IR000000000000000000000000'));
        $this->assertFalse(Iban::passesMod97(null));
    }

    #[Test]
    public function it_extracts_bank_code_and_account_number(): void
    {
        $iban = Iban::make('017', '1234567890123456789');

        $this->assertSame('017', Iban::bankCode($iban));
        $this->assertSame('1234567890123456789', Iban::accountNumber($iban));
        $this->assertSame(24, strlen((string) Iban::digits($iban)));
    }

    #[Test]
    public function it_normalizes_persian_digits(): void
    {
        $iban = Iban::make('019');
        $digits = Iban::digits($iban);
        $this->assertNotNull($digits);

        $persian = strtr($digits, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);

        $this->assertSame('019', Iban::bankCode($persian));
        $this->assertTrue(Iban::passesMod97($persian));
    }
}
