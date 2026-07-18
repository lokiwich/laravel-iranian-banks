<?php

namespace Lokiwich\IranianBanks\Tests;

use Lokiwich\IranianBanks\Support\CardNumber;
use PHPUnit\Framework\Attributes\Test;

class CardNumberTest extends TestCase
{
    #[Test]
    public function it_normalizes_persian_and_arabic_digits(): void
    {
        $this->assertSame('603769', CardNumber::normalize('۶۰۳۷۶۹'));
        $this->assertSame('603769', CardNumber::normalize('٦٠٣٧٦٩'));
        $this->assertSame('603769', CardNumber::normalize('6037-69'));
        $this->assertSame('6037697512345678', CardNumber::normalize('6037 6975 1234 5678'));
    }

    #[Test]
    public function it_extracts_bin_from_card_numbers(): void
    {
        $this->assertSame('603769', CardNumber::bin('6037697512345678'));
        $this->assertSame('603769', CardNumber::bin('۶۰۳۷۶۹'));
        $this->assertNull(CardNumber::bin('60376'));
        $this->assertNull(CardNumber::bin(null));
    }
}
