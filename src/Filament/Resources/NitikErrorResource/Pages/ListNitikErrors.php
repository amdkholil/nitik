<?php

namespace Kholil\Nitik\Filament\Resources\NitikErrorResource\Pages;

use Kholil\Nitik\Filament\Resources\NitikErrorResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Kholil\Nitik\Filament\Resources\NitikErrorResource\Widgets\NitikErrorLevelStats;

class ListNitikErrors extends ListRecords
{
    protected static string $resource = NitikErrorResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            NitikErrorLevelStats::class,
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'unresolved';
    }

    public function getTabs(): array
    {
        $unresolvedCount = \Kholil\Nitik\Models\NitikError::where('is_resolved', false)->count();

        return [
            'all' => Tab::make('All'),
            'unresolved' => Tab::make('Unresolved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_resolved', false))
                ->badge($unresolvedCount ?: null),
            'resolved' => Tab::make('Resolved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_resolved', true)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
