<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Filament\Resources\ParticipantResource\RelationManagers;
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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Exam')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),
                Tables\Columns\TextColumn::make('hsc_roll')
                    ->label('HSC Roll')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->sortable()
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('college')
                    ->label('College')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : 'N/A')
                    ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 50 ? 'warning' : ($state ? 'danger' : 'gray')))
                    ->badge(),
                Tables\Columns\IconColumn::make('completed_at')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn ($record) => $record->isCompleted()),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
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
                        Infolists\Components\TextEntry::make('score')
                            ->label('Score')
                            ->formatStateUsing(fn ($state) => $state ? $state . '%' : 'N/A')
                            ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 50 ? 'warning' : ($state ? 'danger' : 'gray')))
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
                    ->columns(4),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('ip_address')
                            ->label('IP Address'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Registered At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
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
}
