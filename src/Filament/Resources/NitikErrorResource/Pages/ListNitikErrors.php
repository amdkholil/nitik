<?php

namespace Kholil\Nitik\Filament\Resources\NitikErrorResource\Pages;

use Kholil\Nitik\Filament\Resources\NitikErrorResource;
use Filament\Resources\Pages\ListRecords;

class ListNitikErrors extends ListRecords
{
    protected static string $resource = NitikErrorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
