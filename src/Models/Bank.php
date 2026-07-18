<?php

namespace Lokiwich\IranianBanks\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Lokiwich\IranianBanks\Services\BankDetector;
use Lokiwich\IranianBanks\Support\CardNumber;
use Lokiwich\IranianBanks\Support\Iban;

/**
 * @property int $id
 * @property string $slug
 * @property string $name_fa
 * @property string $name_en
 * @property array<int, string> $card_prefixes
 * @property array<int, string> $iban_codes
 * @property string|null $icon
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Bank extends Model
{
    protected $fillable = [
        'slug',
        'name_fa',
        'name_en',
        'card_prefixes',
        'iban_codes',
        'icon',
        'is_active',
    ];

    public function getTable(): string
    {
        return config('iranian-banks.table', 'iranian_banks');
    }

    protected function casts(): array
    {
        return [
            'card_prefixes' => 'array',
            'iban_codes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function findByCardNumber(?string $cardNumber): ?self
    {
        return app(BankDetector::class)->detect($cardNumber);
    }

    public static function findByIban(?string $iban): ?self
    {
        return app(BankDetector::class)->detectFromIban($iban);
    }

    /**
     * Localized display name based on the current app locale.
     */
    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'fa' ? $this->name_fa : $this->name_en;
    }

    /**
     * Primary Sheba bank code (first entry in iban_codes).
     */
    public function primaryIbanCode(): ?string
    {
        $codes = $this->iban_codes ?? [];

        return $codes[0] ?? null;
    }

    /**
     * Absolute path to the SVG file for this bank.
     */
    public function iconPath(string $variant = 'color'): ?string
    {
        $slug = $this->icon ?: $this->slug;

        if ($slug === null || $slug === '') {
            return null;
        }

        $filename = $variant === 'mono'
            ? "{$slug}.svg"
            : "{$slug}-color.svg";

        $base = config('iranian-banks.svg_path') ?: dirname(__DIR__, 2).'/resources/svg';
        $path = rtrim((string) $base, '/').'/'.$filename;

        return is_file($path) ? $path : null;
    }

    /**
     * Raw SVG markup for this bank icon.
     */
    public function iconSvg(string $variant = 'color'): ?string
    {
        $path = $this->iconPath($variant);

        if ($path === null) {
            $path = $this->iconPath($variant === 'color' ? 'mono' : 'color');
        }

        if ($path === null) {
            return null;
        }

        $contents = file_get_contents($path);

        return $contents === false ? null : $contents;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Whether this bank owns the given card BIN / number.
     */
    public function matchesCardNumber(?string $cardNumber): bool
    {
        $bin = CardNumber::bin($cardNumber);

        if ($bin === null) {
            return false;
        }

        return in_array($bin, $this->card_prefixes ?? [], true);
    }

    /**
     * Whether this bank owns the given Sheba / IBAN.
     */
    public function matchesIban(?string $iban): bool
    {
        $bankCode = Iban::bankCode($iban);

        return $bankCode !== null && in_array($bankCode, $this->iban_codes ?? [], true);
    }
}
