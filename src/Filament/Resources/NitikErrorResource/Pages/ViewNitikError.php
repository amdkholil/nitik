<?php

namespace Kholil\Nitik\Filament\Resources\NitikErrorResource\Pages;

use Kholil\Nitik\Filament\Resources\NitikErrorResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Kholil\Nitik\Models\NitikError;

class ViewNitikError extends ViewRecord
{
    protected static string $resource = NitikErrorResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Error Information')
                    ->schema([
                        TextEntry::make('exception_class')
                            ->label('Exception Class')
                            ->badge()
                            ->color('danger'),
                        TextEntry::make('level')
                            ->label('Level')
                            ->badge()
                            ->color(fn (string $state): string => match (strtolower($state)) {
                                'error', 'critical', 'emergency' => 'danger',
                                'warning' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('message')
                            ->label('Message'),
                        TextEntry::make('file')
                            ->label('File'),
                        TextEntry::make('line')
                            ->label('Line'),
                    ])
                    ->columnSpan(1),
                Section::make('Occurrences')
                    ->schema([
                        TextEntry::make('count')
                            ->label('Total Occurrences')
                            ->badge(),
                        TextEntry::make('first_seen_at')
                            ->label('First Seen At')
                            ->dateTime(),
                        TextEntry::make('last_seen_at')
                            ->label('Last Seen At')
                            ->dateTime(),
                        IconEntry::make('is_resolved')
                            ->label('Is Resolved')
                            ->boolean(),
                    ])
                    ->columnSpan(1),
                Section::make('Stack Trace')
                    ->schema([
                        TextEntry::make('stack_trace')
                            ->label('')
                            ->columnSpanFull()
                            ->html()
                            ->formatStateUsing(fn ($state) => 
                                '<div class="bg-gray-950 dark:bg-gray-900 p-4 rounded-lg overflow-y-auto max-h-[400px] border border-gray-800 ring-1 ring-white/5">' .
                                '<code class="font-mono text-sm leading-relaxed whitespace-pre">' . 
                                '<pre>' . e($state) . '</pre>' . 
                                '</code>' .
                                '</div>'
                            ),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
