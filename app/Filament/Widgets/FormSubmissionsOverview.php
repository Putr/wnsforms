<?php

namespace App\Filament\Widgets;

use App\Models\Form;
use App\Models\FormSubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FormSubmissionsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Forms', Form::count()),
            Stat::make('Active Forms', Form::where('is_active', true)->count()),
            Stat::make('Total Submissions', FormSubmission::count()),
            Stat::make('Submissions Today', FormSubmission::whereDate('created_at', now()->toDateString())->count()),
        ];
    }
}
