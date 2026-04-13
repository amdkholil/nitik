<?php

namespace Kholil\Nitik\Filament\Resources;

use Kholil\Nitik\Filament\Resources\NitikErrorResource\Pages;
use Kholil\Nitik\Models\NitikError;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Infolists\Components;
use Filament\Support\Enums\FontFamily;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use BackedEnum;

class NitikErrorResource extends Resource
{
    protected static ?string $model = NitikError::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?string $navigationLabel = 'Error Tracker';

    protected static ?string $slug = 'nitik/errors';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exception_class')
                    ->badge()
                    ->color('danger')
                    ->wrap()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('message')
                    ->wrap()
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->message)
                    ->searchable(),

                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'error', 'critical', 'emergency' => 'danger',
                        'warning' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('count')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state > 10 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('last_seen_at')
                    ->since()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_resolved')
                    ->label('Resolved'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        'ERROR' => 'Error',
                        'CRITICAL' => 'Critical',
                        'EMERGENCY' => 'Emergency',
                        'WARNING' => 'Warning',
                    ]),
                Tables\Filters\TernaryFilter::make('is_resolved')
                    ->label('Resolved')
                    ->default(false),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('markResolved')
                        ->label('Mark Resolved')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (NitikError $record) => $record->update(['is_resolved' => true]))
                        ->visible(fn (NitikError $record) => !$record->is_resolved),
                    DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Actions'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('markResolvedBulk')
                        ->label('Mark Resolved')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_resolved' => true])),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn ($record) => Pages\ViewNitikError::getUrl(['record' => $record]))
            ->defaultSort('last_seen_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNitikErrors::route('/'),
            'view' => Pages\ViewNitikError::route('/{record}'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return config('nitik.navigation_group');
    }
}
