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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use BackedEnum;

class NitikErrorResource extends Resource
{
    protected static ?string $model = NitikError::class;

    protected static ?string $slug = 'nitik/errors';

    public static function getNavigationLabel(): string
    {
        return config('nitik.navigation_label', 'Error Tracker');
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return config('nitik.navigation_icon', 'heroicon-o-bug-ant');
    }

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
                    ->label('Resolved')
                    ->afterStateUpdated(function (\Livewire\Component $livewire) {
                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refresh-sidebar');
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        'ERROR' => 'Error',
                        'CRITICAL' => 'Critical',
                        'EMERGENCY' => 'Emergency',
                        'WARNING' => 'Warning',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('markResolved')
                        ->label('Mark Resolved')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (NitikError $record) => $record->update(['is_resolved' => true]))
                        ->after(function (\Livewire\Component $livewire) {
                            $livewire->dispatch('$refresh');
                            $livewire->dispatch('refresh-sidebar');
                        })
                        ->visible(fn (NitikError $record) => !$record->is_resolved),
                    DeleteAction::make()
                        ->after(function (\Livewire\Component $livewire) {
                            $livewire->dispatch('$refresh');
                            $livewire->dispatch('refresh-sidebar');
                        }),
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
                        ->action(fn (Collection $records) => $records->each->update(['is_resolved' => true]))
                        ->after(function (\Livewire\Component $livewire) {
                            $livewire->dispatch('$refresh');
                            $livewire->dispatch('refresh-sidebar');
                        }),
                    DeleteBulkAction::make()
                        ->after(function (\Livewire\Component $livewire) {
                            $livewire->dispatch('$refresh');
                            $livewire->dispatch('refresh-sidebar');
                        }),
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_resolved', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('is_resolved', false)->exists() ? 'danger' : null;
    }
}
