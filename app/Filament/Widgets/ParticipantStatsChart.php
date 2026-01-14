<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ParticipantStatsChart extends ChartWidget
{
    protected static ?string $heading = 'Participants Activity';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $last7Days = collect(range(6, 0))
            ->map(fn ($days) => Carbon::now()->subDays($days)->format('M d'));

        $participantsStarted = collect(range(6, 0))
            ->map(function ($days) {
                $date = Carbon::now()->subDays($days);
                return Participant::whereDate('started_at', $date)->count();
            });

        $participantsCompleted = collect(range(6, 0))
            ->map(function ($days) {
                $date = Carbon::now()->subDays($days);
                return Participant::whereDate('completed_at', $date)->count();
            });

        return [
            'datasets' => [
                [
                    'label' => 'Started',
                    'data' => $participantsStarted->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Completed',
                    'data' => $participantsCompleted->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $last7Days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
