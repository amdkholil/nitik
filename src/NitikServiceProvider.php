<?php

namespace Kholil\Nitik;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Log;

class NitikServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('nitik')
            ->hasConfigFile()
            ->hasMigration('create_nitik_errors_table')
            ->hasCommands([
                \Kholil\Nitik\Commands\ClearResolvedCommand::class,
                \Kholil\Nitik\Commands\PruneCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->make('log')->extend('nitik', function ($app, array $config) {
            $handler = new \Kholil\Nitik\Handlers\NitikLogHandler();
            
            return new \Monolog\Logger('nitik', [$handler]);
        });
    }
}
