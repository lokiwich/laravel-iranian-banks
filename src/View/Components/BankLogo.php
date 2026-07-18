<?php

namespace Lokiwich\IranianBanks\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Lokiwich\IranianBanks\Models\Bank;

class BankLogo extends Component
{
    public function __construct(
        public ?Bank $bank = null,
        public string $variant = 'color',
        public ?string $class = null,
        public int|string|null $width = 32,
        public int|string|null $height = 32,
    ) {}

    public function render(): View
    {
        return view('iranian-banks::components.bank-logo');
    }

    public function svg(): ?string
    {
        return $this->bank?->iconSvg($this->variant);
    }
}
