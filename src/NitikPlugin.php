<?php

namespace Kholil\Nitik;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Kholil\Nitik\Filament\Resources\NitikErrorResource;

class NitikPlugin implements Plugin
{
    public function getId(): string
    {
        return 'nitik';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            NitikErrorResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
