<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use App\Models\Answer;
use App\Models\Participant;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class Leaderboard extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = ExamResource::class;

    protected static string $view = 'filament.resources.exam-resource.pages.leaderboard';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('merit_position')
                    ->label('Merit')
                    ->sortable()
                    ->default('—')
                    ->badge()
                    ->color('success')
                    ->weight('bold')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->sortable()
                    ->default('—')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),
                Tables\Columns\TextColumn::make('hsc_roll')
                    ->label('HSC Roll')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('college')
                    ->label('College')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state !== null ? $state . ' / ' . $record->participantQuestions()->count() : 'N/A')
                    ->color(fn ($state, $record) => static::getScoreColor($state, $record))
                    ->badge()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('time_taken')
                    ->label('Time Taken')
                    ->getStateUsing(function (Participant $record) {
                        if (!$record->started_at || !$record->completed_at) {
                            return 'N/A';
                        }
                        $seconds = $record->started_at->diffInSeconds($record->completed_at);
                        $minutes = floor($seconds / 60);
                        $remainingSeconds = $seconds % 60;
                        return sprintf('%d:%02d', $minutes, $remainingSeconds);
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("TIMESTAMPDIFF(SECOND, started_at, completed_at) {$direction}");
                    })
                    ->toggleable(),
            ])
            ->defaultSort('merit_position', 'asc')
            ->poll('5s') // Refresh every 5 seconds for real-time updates
            ->emptyStateHeading('No participants yet')
            ->emptyStateDescription('Participants will appear here once they complete the exam.')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        $exam = $this->getRecord();
        
        return Participant::query()
            ->where('exam_id', $exam->id)
            ->whereNotNull('completed_at')
            ->with(['participantQuestions', 'exam']);
    }

    protected static function getScoreColor($state, $record): string
    {
        if ($state === null) {
            return 'gray';
        }
        $total = $record->participantQuestions()->count();
        $percentage = $total > 0 ? ($state / $total) * 100 : 0;
        return $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('recalculate')
                ->label('Recalculate Leaderboard')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Recalculate Leaderboard')
                ->modalDescription('This will recalculate all participant scores and ranks based on current answers. This may take a few moments.')
                ->modalSubmitActionLabel('Recalculate')
                ->action(function () {
                    $exam = $this->getRecord();
                    $service = app(\App\Services\LeaderboardCalculationService::class);
                    $result = $service->calculateLeaderboard($exam);
                    
                    if ($result['success']) {
                        \Filament\Notifications\Notification::make()
                            ->title('Leaderboard Recalculated')
                            ->success()
                            ->body($result['message'])
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Recalculation Failed')
                            ->danger()
                            ->body($result['message'])
                            ->send();
                    }
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'Leaderboard: ' . $this->getRecord()->title;
    }

    public function getHeading(): string
    {
        return 'Leaderboard';
    }

    public function getSubheading(): string | null
    {
        return $this->getRecord()->title;
    }
}
