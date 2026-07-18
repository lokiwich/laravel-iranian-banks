<?php

namespace Lokiwich\IranianBanks\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Lokiwich\IranianBanks\Database\Seeders\IranianBanksSeeder;

class InstallCommand extends Command
{
    protected $signature = 'iranian-banks:install
                            {--fresh : Truncate and re-seed banks}
                            {--no-seed : Skip seeding bank data}';

    protected $description = 'Migrate and seed the Iranian banks directory';

    public function handle(): int
    {
        $this->info('Running Iranian banks migrations...');

        Artisan::call('migrate', [
            '--path' => dirname(__DIR__, 2).'/database/migrations',
            '--realpath' => true,
            '--force' => true,
        ], $this->output);

        if (! $this->option('no-seed') && config('iranian-banks.auto_seed', true)) {
            $this->info('Seeding Iranian banks...');

            $seeder = new IranianBanksSeeder;
            $seeder->setCommand($this);
            $seeder->run(fresh: (bool) $this->option('fresh'));
        }

        $this->components->info('Iranian banks installed successfully.');

        return self::SUCCESS;
    }
}
