<?php

namespace Kholil\Nitik\Tests;

use Filament\FilamentServiceProvider;
use Kholil\Nitik\NitikServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            NitikServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:Hupx3yAySlyS9vFj3u719y5I0E0s9jS092jS092jS09=');

        config()->set('logging.channels.nitik', [
            'driver' => 'nitik',
        ]);
    }
}
