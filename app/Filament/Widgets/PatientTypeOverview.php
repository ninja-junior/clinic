<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class PatientTypeOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Males', Patient::query()->where('type', 'male')->count()),
            Stat::make('Females', Patient::query()->where('type', 'female')->count()),
            Stat::make('Others', Patient::query()->where('type', 'other')->count()),
        ];
    }
}
