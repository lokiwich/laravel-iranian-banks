<?php

namespace Lokiwich\IranianBanks\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lokiwich\IranianBanks\Database\Seeders\IranianBanksSeeder;
use Lokiwich\IranianBanks\IranianBanksServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        (new IranianBanksSeeder)->run();
    }

    protected function getPackageProviders($app): array
    {
        return [
            IranianBanksServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
