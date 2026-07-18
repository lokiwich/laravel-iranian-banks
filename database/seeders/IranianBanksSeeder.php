<?php

namespace Lokiwich\IranianBanks\Database\Seeders;

use Illuminate\Database\Seeder;
use Lokiwich\IranianBanks\Models\Bank;

class IranianBanksSeeder extends Seeder
{
    public function run(bool $fresh = false): void
    {
        if ($fresh) {
            Bank::query()->delete();
        }

        $banks = require dirname(__DIR__).'/data/banks.php';
        $slugs = [];

        foreach ($banks as $bank) {
            $slugs[] = $bank['slug'];

            Bank::query()->updateOrCreate(
                ['slug' => $bank['slug']],
                [
                    'name_fa' => $bank['name_fa'],
                    'name_en' => $bank['name_en'],
                    'card_prefixes' => $bank['card_prefixes'],
                    'iban_codes' => $bank['iban_codes'] ?? [],
                    'icon' => $bank['icon'] ?? $bank['slug'],
                    'is_active' => $bank['is_active'] ?? true,
                ]
            );
        }

        Bank::query()->whereNotIn('slug', $slugs)->delete();

        if ($this->command) {
            $this->command->info('Seeded '.count($banks).' Iranian banks.');
        }
    }
}
