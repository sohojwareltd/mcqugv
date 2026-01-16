<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use App\Services\LeaderboardCalculationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewLeaderboard')
                ->label('View Leaderboard')
                ->icon('heroicon-o-trophy')
                ->color('primary')
                ->url(fn () => static::getResource()::getUrl('leaderboard', ['record' => $this->record]))
                ->visible(fn () => $this->record->participants()->whereNotNull('completed_at')->count() > 0),
            Actions\Action::make('calculateLeaderboard')
                ->label('Calculate Leaderboard')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Calculate Leaderboard')
                ->modalDescription('This will calculate and assign ranks to all participants based on the following criteria (in order):' . PHP_EOL . PHP_EOL .
                    '1. Total Score (highest first)' . PHP_EOL .
                    '2. Completion Time (fastest first)' . PHP_EOL .
                    '3. Category Scores: Math → English → Bangla → ICT → General Knowledge' . PHP_EOL . PHP_EOL .
                    'This action will update all participant ranks and merit positions. You can recalculate if needed.')
                ->modalSubmitActionLabel('Calculate Now')
                ->visible(fn () => $this->record->end_time && $this->record->end_time->isPast())
                ->disabled(fn () => $this->record->participants()->whereNotNull('completed_at')->count() === 0)
                ->action(function () {
                    try {
                        $service = new LeaderboardCalculationService();
                        $result = $service->calculateLeaderboard($this->record);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Leaderboard Calculated')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Calculation Failed')
                                ->body($result['message'])
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to calculate leaderboard: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
