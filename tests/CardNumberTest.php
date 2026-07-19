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

    #[Test]
    public function it_picks_the_longest_matching_prefix(): void
    {
        $prefixes = ['621986', '62198619'];

        $this->assertSame('621986', CardNumber::bestMatchingPrefix('621986', $prefixes));
        $this->assertSame('621986', CardNumber::bestMatchingPrefix('6219860012345678', $prefixes));
        $this->assertSame('62198619', CardNumber::bestMatchingPrefix('62198619', $prefixes));
        $this->assertSame('62198619', CardNumber::bestMatchingPrefix('6219861912345678', $prefixes));
        $this->assertNull(CardNumber::bestMatchingPrefix('603769', $prefixes));
        $this->assertNull(CardNumber::bestMatchingPrefix('62198', $prefixes));
        $this->assertNull(CardNumber::bestMatchingPrefix(null, $prefixes));
    }
}
