<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Category;
use App\Models\Exam;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Questions';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Question Information')
                    ->schema([
                        Forms\Components\Select::make('exam_id')
                            ->label('Exam')
                            ->relationship('exam', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('category_id', null))
                            ->columnSpan(1),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name', fn ($query, $get) => 
                                $query->where('is_active', true)
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('question_text')
                            ->label('Question Text')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Enter the question text. You can use LaTeX math: $x^2$ for inline or $$x^2$$ for block math.')
                            ->helperText('Supports LaTeX math notation and Bangla text'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active questions will be included in exams')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Answer Options')
                    ->description('Add multiple choice options. At least one option must be marked as correct.')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->relationship('options')
                            ->schema([
                                Forms\Components\Textarea::make('option_text')
                                    ->label('Option Text')
                                    ->required()
                                    ->rows(2)
                                    ->columnSpan(2)
                                    ->placeholder('Enter option text')
                                    ->helperText('Supports LaTeX math and Bangla text'),

                                Forms\Components\Toggle::make('is_correct')
                                    ->label('Correct Answer')
                                    ->default(false)
                                    ->inline(false)
                                    ->columnSpan(1)
                                    ->helperText('Mark this option as the correct answer'),
                            ])
                            ->columns(3)
                            ->defaultItems(4)
                            ->minItems(2)
                            ->maxItems(10)
                            ->addActionLabel('Add Option')
                            ->itemLabel(fn (array $state): ?string => 
                                $state['option_text'] 
                                    ? \Str::limit($state['option_text'], 30) . ($state['is_correct'] ?? false ? ' ✓' : '')
                                    : 'New Option'
                            )
                            ->collapsible()
                            ->cloneable()
                            ->deletable(true)
                            ->reorderable(true)
                            ->columnSpanFull()
                            ->required()
                            ->validationMessages([
                                'min' => 'At least 2 options are required',
                                'max' => 'Maximum 10 options allowed',
                            ]),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Exam')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('question_text')
                    ->label('Question')
                    ->sortable()
                    ->searchable()
                    ->limit(80)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options')
                    ->counts('options')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->relationship('exam', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
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
                Infolists\Components\Section::make('Question Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('exam.title')
                            ->label('Exam')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('category.name')
                            ->label('Category')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('question_text')
                            ->label('Question Text')
                            ->columnSpanFull()
                            ->html(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Answer Options')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('options')
                            ->schema([
                                Infolists\Components\TextEntry::make('option_text')
                                    ->label('Option')
                                    ->columnSpan(2),
                                Infolists\Components\IconEntry::make('is_correct')
                                    ->label('Correct')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('gray'),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'view' => Pages\ViewQuestion::route('/{record}'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
