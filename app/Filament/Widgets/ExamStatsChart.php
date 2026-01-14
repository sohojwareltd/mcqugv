<?php

namespace App\Filament\Widgets;

use App\Models\Exam;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ExamStatsChart extends ChartWidget
{
    protected static ?string $heading = 'Exams Overview';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $last30Days = collect(range(29, 0))
            ->map(fn ($days) => Carbon::now()->subDays($days)->format('M d'));

        $examsCreated = collect(range(29, 0))
            ->map(function ($days) {
                $date = Carbon::now()->subDays($days);
                return Exam::whereDate('created_at', $date)->count();
            });

        return [
            'datasets' => [
                [
                    'label' => 'Exams Created',
                    'data' => $examsCreated->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $last30Days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
