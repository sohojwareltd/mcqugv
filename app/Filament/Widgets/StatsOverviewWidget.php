<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Exam;
use App\Models\Participant;
use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalExams = Exam::count();
        $activeExams = Exam::where('is_active', true)->count();
        $totalParticipants = Participant::count();
        $completedParticipants = Participant::whereNotNull('completed_at')->count();
        $totalQuestions = Question::count();
        $activeQuestions = Question::where('is_active', true)->count();
        $totalCategories = Category::count();
        $activeCategories = Category::where('is_active', true)->count();

        $avgScore = Participant::whereNotNull('score')
            ->avg('score') ?? 0;

        return [
            Stat::make('Total Exams', $totalExams)
                ->description($activeExams . ' active')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->chart([$totalExams, $activeExams]),

            Stat::make('Total Participants', $totalParticipants)
                ->description($completedParticipants . ' completed')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart([$totalParticipants, $completedParticipants]),

            Stat::make('Total Questions', $totalQuestions)
                ->description($activeQuestions . ' active')
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('warning')
                ->chart([$totalQuestions, $activeQuestions]),

            Stat::make('Categories', $totalCategories)
                ->description($activeCategories . ' active')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),

            Stat::make('Average Score', number_format($avgScore, 1) . '%')
                ->description('Across all exams')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),
        ];
    }
}
