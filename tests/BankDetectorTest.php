<?php

namespace Lokiwich\IranianBanks\Tests;

use Lokiwich\IranianBanks\Facades\IranianBanks;
use Lokiwich\IranianBanks\Models\Bank;
use Lokiwich\IranianBanks\Support\CardNumber;
use Lokiwich\IranianBanks\Support\Iban;
use PHPUnit\Framework\Attributes\Test;

class BankDetectorTest extends TestCase
{
    #[Test]
    public function it_detects_bank_from_six_digit_bin(): void
    {
        $bank = IranianBanks::detect('603769');

        $this->assertNotNull($bank);
        $this->assertSame('saderat', $bank->slug);
        $this->assertSame('بانک صادرات ایران', $bank->name_fa);
    }

    #[Test]
    public function it_detects_bank_from_full_card_number(): void
    {
        $bank = Bank::findByCardNumber('6037697512345678');

        $this->assertNotNull($bank);
        $this->assertSame('saderat', $bank->slug);
    }

    #[Test]
    public function it_detects_bank_with_persian_digits(): void
    {
        $bank = IranianBanks::detect('۶۰۳۷۹۹۱۲۳۴۵۶۷۸۹۰');

        $this->assertNotNull($bank);
        $this->assertSame('melli', $bank->slug);
    }

    #[Test]
    public function it_supports_multiple_bins_on_one_bank(): void
    {
        $first = IranianBanks::detect('622106');
        $second = IranianBanks::detect('639194');
        $third = IranianBanks::detect('627884');

        $this->assertNotNull($first);
        $this->assertSame('parsian', $first->slug);
        $this->assertSame($first->id, $second?->id);
        $this->assertSame($first->id, $third?->id);
    }

    #[Test]
    public function it_returns_null_for_unknown_or_short_input(): void
    {
        $this->assertNull(IranianBanks::detect('000000'));
        $this->assertNull(IranianBanks::detect('12345'));
        $this->assertNull(IranianBanks::detect(null));
        $this->assertNull(IranianBanks::detect(''));
    }

    #[Test]
    public function it_reads_icon_svg_markup(): void
    {
        $bank = IranianBanks::detect('610433');

        $this->assertNotNull($bank);
        $svg = $bank->iconSvg('color');

        $this->assertNotNull($svg);
        $this->assertStringContainsString('<svg', $svg);
    }

    #[Test]
    public function it_matches_card_number_on_model(): void
    {
        $bank = Bank::query()->where('slug', 'pasargad')->firstOrFail();

        $this->assertTrue($bank->matchesCardNumber('5022291234567890'));
        $this->assertTrue($bank->matchesCardNumber('639347'));
        $this->assertFalse($bank->matchesCardNumber('603769'));
    }

    #[Test]
    public function card_number_luhn_rejects_invalid_numbers(): void
    {
        $this->assertFalse(CardNumber::passesLuhn('1234567890123456'));
        $this->assertFalse(CardNumber::passesLuhn('603769'));
    }

    #[Test]
    public function card_number_luhn_accepts_valid_checksum(): void
    {
        // Construct a valid 16-digit Luhn number starting with a known BIN prefix pattern.
        $base = '603769751234567';
        for ($check = 0; $check <= 9; $check++) {
            $candidate = $base.$check;
            if (CardNumber::passesLuhn($candidate)) {
                $this->assertTrue(CardNumber::passesLuhn($candidate));

                return;
            }
        }

        $this->fail('Expected to find a valid Luhn check digit.');
    }

    #[Test]
    public function it_detects_bank_from_iban_code(): void
    {
        $iban = Iban::make('012');

        $bank = IranianBanks::detectFromIban($iban);

        $this->assertNotNull($bank);
        $this->assertSame('mellat', $bank->slug);
        $this->assertTrue($bank->matchesIban($iban));
        $this->assertSame($bank->id, Bank::findByIban($iban)?->id);
    }

    #[Test]
    public function it_returns_null_for_unknown_iban_bank_code(): void
    {
        $iban = Iban::make('999');

        $this->assertTrue(Iban::passesMod97($iban));
        $this->assertNull(IranianBanks::detectFromIban($iban));
    }

    #[Test]
    public function it_seeds_user_provided_iban_codes(): void
    {
        $expected = [
            'mellat' => '012',
            'melli' => '017',
            'saderat' => '019',
            'sepah' => '015',
            'tejarat' => '018',
            'keshavarzi' => '016',
            'maskan' => '014',
            'post' => '021',
            'tosee-saderat' => '020',
            'pasargad' => '057',
            'parsian' => '054',
            'eghtesad-novin' => '055',
            'refah' => '013',
        ];

        foreach ($expected as $slug => $code) {
            $bank = Bank::query()->where('slug', $slug)->first();

            $this->assertNotNull($bank);
            $this->assertContains($code, $bank->iban_codes ?? []);
        }
    }

    #[Test]
    public function it_folds_merged_banks_into_sepah(): void
    {
        $this->assertDatabaseMissing('iranian_banks', ['slug' => 'ansar']);
        $this->assertDatabaseHas('iranian_banks', ['slug' => 'blu']);

        $sepah = Bank::query()->where('slug', 'sepah')->firstOrFail();

        $this->assertContains('627381', $sepah->card_prefixes);
        $this->assertContains('063', $sepah->iban_codes);
        $this->assertSame('sepah', IranianBanks::detect('627381')?->slug);
        $this->assertSame('sepah', IranianBanks::detectFromIban(Iban::make('063'))?->slug);
    }

    #[Test]
    public function it_prefers_saman_over_blu_for_shared_bin(): void
    {
        $this->assertSame('saman', IranianBanks::detect('621986')?->slug);
        $this->assertSame('saman', IranianBanks::detectFromIban(Iban::make('056'))?->slug);
        $this->assertDatabaseHas('iranian_banks', ['slug' => 'blu']);
    }
}
