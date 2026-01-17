<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ParticipantExportExporter;
use App\Filament\Resources\ParticipantResource\Pages;
use App\Filament\Resources\ParticipantResource\RelationManagers;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\Participant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Participants';

    protected static ?string $navigationGroup = 'Participants';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Participant Information')
                    ->schema([
                        Forms\Components\Select::make('exam_id')
                            ->label('Exam')
                            ->relationship('exam', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('full_name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('hsc_roll')
                            ->label('HSC Roll')
                            ->required()
                            ->maxLength(50)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('hsc_passing_year')
                            ->label('HSC Passing Year')
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(now()->year)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('group')
                            ->label('Group')
                            ->maxLength(50)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('board')
                            ->label('Board')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('college')
                            ->label('College')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Exam Details')
                    ->schema([
                        Forms\Components\TextInput::make('score')
                            ->label('Score (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Started At')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->maxLength(45)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->default('—')
                    ->width('60px'),
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->width('60px'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->limit(25),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone')
                    ->width('120px'),
                Tables\Columns\TextColumn::make('hsc_roll')
                    ->label('HSC Roll')
                    ->sortable()
                    ->searchable()
                    ->width('100px'),
                Tables\Columns\TextColumn::make('hsc_passing_year')
                    ->label('Year')
                    ->sortable()
                    ->searchable()
                    ->width('70px'),
                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->width('90px'),
                Tables\Columns\TextColumn::make('college')
                    ->label('College')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Exam')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state !== null ? $state . '/' . $record->participantQuestions()->count() : 'N/A')
                    ->color(fn ($state, $record) => static::getScoreColor($state, $record))
                    ->badge()
                    ->width('80px'),
                Tables\Columns\IconColumn::make('completed_at')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn ($record) => $record->isCompleted())
                    ->width('80px'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->relationship('exam', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('completed')
                    ->label('Completion Status')
                    ->query(fn ($query) => $query->whereNotNull('completed_at')),
                Tables\Filters\Filter::make('in_progress')
                    ->label('In Progress')
                    ->query(fn ($query) => $query->whereNull('completed_at')->whereNotNull('started_at')),
                Tables\Filters\Filter::make('not_started')
                    ->label('Not Started')
                    ->query(fn ($query) => $query->whereNull('started_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('recalculateScore')
                    ->label('Recalculate Score')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Recalculate Score')
                    ->modalDescription('This will recalculate the participant\'s score by counting only correct answers for questions in their paper. Continue?')
                    ->modalSubmitActionLabel('Recalculate')
                    ->action(function (Participant $record) {
                        // Get question IDs that belong to this participant's paper
                        $participantQuestionIds = $record->participantQuestions()->pluck('question_id');
                        
                        // Count only correct answers for questions in participant's paper
                        $score = Answer::where('participant_id', $record->id)
                            ->whereIn('question_id', $participantQuestionIds)
                            ->where('is_correct', true)
                            ->count();
                        
                        // Update the score
                        $record->update(['score' => $score]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Score Recalculated')
                            ->success()
                            ->body("Participant's score has been recalculated: {$score} / {$participantQuestionIds->count()}")
                            ->send();
                    })
                    ->visible(fn (Participant $record) => $record->participantQuestions()->count() > 0),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(ParticipantExportExporter::class)
                    ->label('Export to Excel'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(ParticipantExportExporter::class),
                ]),
            ])
            ->defaultSort('rank', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Participant Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Full Name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('exam.title')
                            ->label('Exam')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')
                            ->copyable()
                            ->icon('heroicon-m-phone'),
                        Infolists\Components\TextEntry::make('hsc_roll')
                            ->label('HSC Roll'),
                        Infolists\Components\TextEntry::make('hsc_passing_year')
                            ->label('HSC Passing Year'),
                        Infolists\Components\TextEntry::make('group')
                            ->label('Group')
                            ->badge(),
                        Infolists\Components\TextEntry::make('board')
                            ->label('Board'),
                        Infolists\Components\TextEntry::make('college')
                            ->label('College')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Exam Results')
                    ->schema([
                        Infolists\Components\TextEntry::make('rank')
                            ->label('Rank')
                            ->badge()
                            ->color('primary')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->placeholder('Not ranked'),
                        Infolists\Components\TextEntry::make('merit_position')
                            ->label('Merit Position')
                            ->badge()
                            ->color('success')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->placeholder('Not ranked'),
                        Infolists\Components\TextEntry::make('score')
                            ->label('Score')
                            ->formatStateUsing(fn ($state, $record) => static::formatScore($state, $record))
                            ->color(fn ($state, $record) => static::getScoreColorForInfolist($state, $record))
                            ->badge()
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\IconEntry::make('completed_at')
                            ->label('Status')
                            ->getStateUsing(fn ($record) => $record->isCompleted())
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-clock')
                            ->trueColor('success')
                            ->falseColor('warning'),
                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Started At')
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Not completed'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('IP Address & Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->copyable()
                            ->icon('heroicon-o-globe-alt'),
                        Infolists\Components\TextEntry::make('ip_location')
                            ->label('Location')
                            ->getStateUsing(function ($record) {
                                if (!$record->ip_address || $record->ip_address === '127.0.0.1' || $record->ip_address === '::1') {
                                    return 'Local/Private IP';
                                }
                                return static::getIpLocation($record->ip_address);
                            })
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('Unable to determine location'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Questions & Answers')
                    ->description('All questions answered by this participant')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('participantQuestions')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('order_no')
                                    ->label('Q#')
                                    ->badge()
                                    ->color('primary')
                                    ->formatStateUsing(fn ($state) => 'Q' . $state),
                                Infolists\Components\TextEntry::make('question.question_text')
                                    ->label('Question')
                                    ->html()
                                    ->columnSpan(3)
                                    ->limit(200),
                                Infolists\Components\TextEntry::make('question.category.name')
                                    ->label('Category')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('participant_answer')
                                    ->label('Participant Answer')
                                    ->getStateUsing(function ($record) {
                                        // $record is ParticipantQuestion, get participant through relationship
                                        $participantId = $record->participant_id;
                                        
                                        $answer = \App\Models\Answer::where('participant_id', $participantId)
                                            ->where('question_id', $record->question_id)
                                            ->with('option')
                                            ->first();
                                        
                                        if (!$answer || !$answer->option) {
                                            return 'Not answered';
                                        }
                                        
                                        $isCorrect = $answer->is_correct;
                                        $optionText = $answer->option->option_text;
                                        $icon = $isCorrect ? '✓' : '✗';
                                        
                                        return ($isCorrect ? '✓ ' : '✗ ') . $optionText;
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $participantId = $record->participant_id;
                                        $answer = \App\Models\Answer::where('participant_id', $participantId)
                                            ->where('question_id', $record->question_id)
                                            ->first();
                                        
                                        if (!$answer) {
                                            return 'gray';
                                        }
                                        
                                        return $answer->is_correct ? 'success' : 'danger';
                                    })
                                    ->icon(function ($record) {
                                        $participantId = $record->participant_id;
                                        $answer = \App\Models\Answer::where('participant_id', $participantId)
                                            ->where('question_id', $record->question_id)
                                            ->first();
                                        
                                        if (!$answer) {
                                            return null;
                                        }
                                        
                                        return $answer->is_correct ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
                                    })
                                    ->columnSpan(2),
                                Infolists\Components\TextEntry::make('correct_answer')
                                    ->label('Correct Answer')
                                    ->getStateUsing(function ($record) {
                                        $correctOption = $record->question->options()->where('is_correct', true)->first();
                                        return $correctOption ? $correctOption->option_text : 'N/A';
                                    })
                                    ->badge()
                                    ->color('success')
                                    ->columnSpan(2),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('attempt_token')
                            ->label('Attempt Token')
                            ->copyable()
                            ->placeholder('N/A')
                            ->limit(50)
                            ->tooltip(fn ($record) => $record->attempt_token)
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Registered At')
                            ->dateTime('d/m/Y H:i')
                            ->columnSpan(1),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d/m/Y H:i')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    protected static function getIpLocation(string $ip): string
    {
        try {
            // Use ip-api.com free service (45 requests/minute limit)
            $response = \Illuminate\Support\Facades\Http::timeout(3)
                ->get("http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,isp");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    $location = [];
                    if (!empty($data['city'])) $location[] = $data['city'];
                    if (!empty($data['regionName'])) $location[] = $data['regionName'];
                    if (!empty($data['country'])) $location[] = $data['country'];
                    
                    $locationStr = implode(', ', $location);
                    if (!empty($data['isp'])) {
                        $locationStr .= ' (' . $data['isp'] . ')';
                    }
                    
                    return $locationStr ?: 'Unknown location';
                }
            }
        } catch (\Exception $e) {
            \Log::warning('IP geolocation failed', ['ip' => $ip, 'error' => $e->getMessage()]);
        }
        
        return 'Unable to determine location';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'view' => Pages\ViewParticipant::route('/{record}'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
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

    protected static function formatScore($state, $record): string
    {
        if ($state === null) {
            return 'N/A';
        }
        $total = $record->participantQuestions()->count();
        $percentage = $total > 0 ? round(($state / $total) * 100, 1) : 0;
        return $state . ' / ' . $total . ' (' . $percentage . '%)';
    }

    protected static function getScoreColorForInfolist($state, $record): string
    {
        if ($state === null) {
            return 'gray';
        }
        $total = $record->participantQuestions()->count();
        $percentage = $total > 0 ? ($state / $total) * 100 : 0;
        return $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
    }
}
