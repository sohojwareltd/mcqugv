<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ParticipantPerformanceWidget extends ChartWidget
{
    protected static ?string $heading = 'Participant Performance Distribution';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $participants = Participant::whereNotNull('score')
            ->get();

        $ranges = [
            '90-100' => 0,
            '80-89' => 0,
            '70-79' => 0,
            '60-69' => 0,
            '50-59' => 0,
            '0-49' => 0,
        ];

        foreach ($participants as $participant) {
            $score = $participant->score;
            if ($score >= 90) {
                $ranges['90-100']++;
            } elseif ($score >= 80) {
                $ranges['80-89']++;
            } elseif ($score >= 70) {
                $ranges['70-79']++;
            } elseif ($score >= 60) {
                $ranges['60-69']++;
            } elseif ($score >= 50) {
                $ranges['50-59']++;
            } else {
                $ranges['0-49']++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Participants',
                    'data' => array_values($ranges),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // 90-100: green
                        'rgba(59, 130, 246, 0.8)',  // 80-89: blue
                        'rgba(168, 85, 247, 0.8)',  // 70-79: purple
                        'rgba(251, 191, 36, 0.8)',  // 60-69: yellow
                        'rgba(249, 115, 22, 0.8)',  // 50-59: orange
                        'rgba(239, 68, 68, 0.8)',   // 0-49: red
                    ],
                ],
            ],
            'labels' => array_keys($ranges),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
