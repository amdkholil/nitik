<?php

namespace Kholil\Nitik\Filament\Resources\NitikErrorResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Kholil\Nitik\Models\NitikError;

class NitikErrorLevelStats extends BaseWidget
{
    protected function getStats(): array
    {
        $dbLevels = NitikError::query()
            ->selectRaw('level, count(*) as total')
            ->groupBy('level')
            ->pluck('total', 'level')
            ->mapWithKeys(fn ($count, $level) => [strtolower($level) => $count])
            ->toArray();

        $levels = [
            'emergency' => ['color' => 'danger', 'icon' => 'heroicon-o-exclamation-triangle'],
            'critical' => ['color' => 'danger', 'icon' => 'heroicon-o-x-circle'],
            'error' => ['color' => 'danger', 'icon' => 'heroicon-o-x-mark'],
            'warning' => ['color' => 'warning', 'icon' => 'heroicon-o-exclamation-circle'],
        ];

        foreach ($dbLevels as $level => $count) {
            if (!isset($levels[$level])) {
                $levels[$level] = ['color' => 'gray', 'icon' => 'heroicon-o-question-mark-circle'];
            }
        }

        $unresolvedLevels = NitikError::query()
            ->where('is_resolved', false)
            ->selectRaw('level, count(*) as total')
            ->groupBy('level')
            ->pluck('total', 'level')
            ->mapWithKeys(fn ($count, $level) => [strtolower($level) => $count])
            ->toArray();

        $stats = [];
        foreach ($levels as $level => $config) {
            $unresolvedCount = $unresolvedLevels[$level] ?? 0;
            $description = $unresolvedCount > 0 
                ? "{$unresolvedCount} unresolved" 
                : 'All solved';

            $stats[] = Stat::make(ucfirst($level), $dbLevels[$level] ?? 0)
                ->description($description)
                ->descriptionIcon($config['icon'])
                ->color($config['color'])
                ->icon($config['icon']);
        }

        return $stats;
    }
}
