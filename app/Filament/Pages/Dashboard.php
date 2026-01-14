<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ExamReportWidget;
use App\Filament\Widgets\ExamStatsChart;
use App\Filament\Widgets\ParticipantPerformanceWidget;
use App\Filament\Widgets\ParticipantStatsChart;
use App\Filament\Widgets\RecentParticipantsWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            ExamStatsChart::class,
            ParticipantStatsChart::class,
            ParticipantPerformanceWidget::class,
            RecentParticipantsWidget::class,
            ExamReportWidget::class,
        ];
    }
}
