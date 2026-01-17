<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Filament\Resources\ExamResource\RelationManagers;
use App\Models\Category;
use App\Models\Exam;
use App\Models\ExamCategoryRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Exams';

    protected static ?string $navigationGroup = 'Exam Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Exam Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Exam Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->placeholder('Enter exam title'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Exam Schedule')
                    ->description('Set the start and end times for the exam')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('Asia/Dhaka')
                            ->helperText('When the exam becomes available to participants')
                            ->minDate(fn ($record) => $record ? null : now())
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('Asia/Dhaka')
                            ->helperText('When the exam will be closed')
                            ->minDate(function (Forms\Get $get, $record) {
                                // When editing existing exam, don't restrict dates
                                if ($record) {
                                    return null;
                                }
                                // When creating, use start_time + 1 minute or now()
                                $startTime = $get('start_time');
                                if ($startTime) {
                                    try {
                                        return \Carbon\Carbon::parse($startTime)->addMinute();
                                    } catch (\Exception $e) {
                                        return now();
                                    }
                                }
                                return now();
                            })
                            ->rules([
                                'required',
                                'after:start_time',
                            ])
                            ->columnSpan(1)
                            ->dehydrated(true), // Ensure value is always saved
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Exam Settings')
                    ->schema([
                        Forms\Components\TextInput::make('total_questions')
                            ->label('Total Questions')
                            ->numeric()
                            ->default(20)
                            ->minValue(1)
                            ->maxValue(1000)
                            ->required()
                            ->helperText('Expected total number of questions for this exam')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('result_publish_at')
                            ->label('Result Publish Time')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('Asia/Dhaka')
                            ->helperText('When results will be published (optional)')
                            ->minDate(function (Forms\Get $get, $record) {
                                // When editing existing exam, don't restrict dates
                                if ($record) {
                                    return null;
                                }
                                // When creating, use end_time + 1 minute or now()
                                $endTime = $get('end_time');
                                if ($endTime) {
                                    try {
                                        return \Carbon\Carbon::parse($endTime)->addMinute();
                                    } catch (\Exception $e) {
                                        return now();
                                    }
                                }
                                return now();
                            })
                            ->rules([
                                'nullable',
                                'after:end_time',
                            ])
                            ->columnSpan(1)
                            ->dehydrated(true), // Ensure value is always saved

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Activate this exam to make it available')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Category Rules')
                    ->description('Define how many questions from each category should be included in this exam. The sum of all category question counts should match the total questions.')
                    ->schema([
                        Forms\Components\Repeater::make('categoryRules')
                            ->relationship('categoryRules')
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->options(fn () => Category::where('is_active', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        // Reset question count when category changes
                                        $set('question_count', 1);
                                    })
                                    ->columnSpan(2)
                                    ->helperText('Select a category'),

                                Forms\Components\TextInput::make('question_count')
                                    ->label('Question Count')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(1000)
                                    ->default(1)
                                    ->helperText('Number of questions from this category')
                                    ->columnSpan(1)
                                    ->reactive(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Add Category Rule')
                            ->itemLabel(fn (array $state): ?string => 
                                $state['category_id'] 
                                    ? Category::find($state['category_id'])?->name . ' (' . ($state['question_count'] ?? 0) . ' questions)'
                                    : 'New Category Rule'
                            )
                            ->collapsible()
                            ->cloneable()
                            ->deletable(true)
                            ->reorderable(true)
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                // Calculate total from category rules
                                $total = collect($state)->sum('question_count');
                                $set('total_questions', $total > 0 ? $total : $get('total_questions'));
                            }),

                        Forms\Components\Placeholder::make('category_rules_info')
                            ->label('')
                            ->content(function (Forms\Get $get) {
                                $rules = $get('categoryRules') ?? [];
                                if (empty($rules)) {
                                    return 'No category rules added yet. Add rules to define question distribution.';
                                }
                                
                                $total = collect($rules)->sum('question_count');
                                $expected = $get('total_questions') ?? 0;
                                $match = $total == $expected && $total > 0;
                                
                                $status = $match ? '✓ Match' : '⚠ Mismatch';
                                $color = $match ? 'text-success-600' : 'text-warning-600';
                                
                                return "Total from rules: {$total} | Expected: {$expected} | {$status}";
                            })
                            ->visible(fn (Forms\Get $get) => !empty($get('categoryRules')))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->width('60px'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->limit(40),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->width('70px'),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->width('140px')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('End')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->width('140px')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_questions')
                    ->label('Q')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->width('60px'),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label('Participants')
                    ->counts('participants')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->width('100px'),
                Tables\Columns\TextColumn::make('categoryRules_count')
                    ->label('Rules')
                    ->counts('categoryRules')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->width('70px')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('result_publish_at')
                    ->label('Publish')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn ($state) => $state && $state->isPast() ? 'success' : 'warning')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
                Infolists\Components\Section::make('Exam Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('Title')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('total_questions')
                            ->label('Total Questions')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Schedule')
                    ->schema([
                        Infolists\Components\TextEntry::make('start_time')
                            ->label('Start Time')
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('end_time')
                            ->label('End Time')
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('result_publish_at')
                            ->label('Result Publish Time')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Not set')
                            ->color(fn ($state) => $state && $state->isPast() ? 'success' : 'warning'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Category Rules')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('categoryRules')
                            ->schema([
                                Infolists\Components\TextEntry::make('category.name')
                                    ->label('Category')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('question_count')
                                    ->label('Question Count')
                                    ->badge()
                                    ->color('primary'),
                            ])
                            ->columns(2),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('participants_count')
                            ->label('Total Participants')
                            ->getStateUsing(fn ($record) => $record->participants()->count())
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('completed_participants_count')
                            ->label('Completed')
                            ->getStateUsing(fn ($record) => $record->participants()->whereNotNull('completed_at')->count())
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('leaderboard_status')
                            ->label('Leaderboard Status')
                            ->getStateUsing(fn ($record) => static::getLeaderboardStatus($record))
                            ->badge()
                            ->color(fn ($record) => static::getLeaderboardStatusColor($record)),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view' => Pages\ViewExam::route('/{record}'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
            'leaderboard' => Pages\Leaderboard::route('/{record}/leaderboard'),
        ];
    }

    protected static function getLeaderboardStatus($record): string
    {
        $rankedCount = $record->participants()->whereNotNull('rank')->count();
        $completedCount = $record->participants()->whereNotNull('completed_at')->count();
        
        if ($rankedCount === 0 && $completedCount > 0) {
            return 'Not Calculated';
        } elseif ($rankedCount > 0) {
            return "Calculated ({$rankedCount} participants ranked)";
        }
        return 'No completed participants';
    }

    protected static function getLeaderboardStatusColor($record): string
    {
        $rankedCount = $record->participants()->whereNotNull('rank')->count();
        return $rankedCount > 0 ? 'success' : 'warning';
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewExam::class,
            Pages\EditExam::class,
            Pages\Leaderboard::class,
        ]);
    }
}
