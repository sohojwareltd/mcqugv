<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UniqueParticipantResource\Pages;
use App\Models\Participant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UniqueParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Unique Participants';

    protected static ?string $navigationGroup = 'Participants';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        // Get unique participants by HSC roll (get the latest record for each HSC roll)
        return parent::getEloquentQuery()
            ->whereNotNull('hsc_roll')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('participants')
                    ->whereNotNull('hsc_roll')
                    ->groupBy('hsc_roll');
            });
    }

    public static function form(Form $form): Form
    {
        // No form needed - this is read-only
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->limit(30),
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
                    ->label('HSC Year')
                    ->sortable()
                    ->searchable()
                    ->width('80px'),
                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->width('90px'),
                Tables\Columns\TextColumn::make('board')
                    ->label('Board')
                    ->sortable()
                    ->searchable()
                    ->width('100px'),
                Tables\Columns\TextColumn::make('college')
                    ->label('College')
                    ->searchable()
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Group')
                    ->options([
                        'Science' => 'Science',
                        'Arts' => 'Arts (Humanities)',
                        'Commerce' => 'Commerce (Business Studies)',
                    ]),
                Tables\Filters\SelectFilter::make('board')
                    ->label('Board')
                    ->options([
                        'Dhaka' => 'Dhaka',
                        'Rajshahi' => 'Rajshahi',
                        'Jessore' => 'Jessore',
                        'Comilla' => 'Comilla',
                        'Chittagong' => 'Chittagong',
                        'Barishal' => 'Barishal',
                        'Sylhet' => 'Sylhet',
                        'Dinajpur' => 'Dinajpur',
                        'Mymensingh' => 'Mymensingh',
                        'Technical' => 'Technical',
                        'Madrasah' => 'Madrasah',
                    ])
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make(),
                ]),
            ])
            ->defaultSort('full_name', 'asc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUniqueParticipants::route('/'),
            'view' => Pages\ViewUniqueParticipant::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Full Name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
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
            ]);
    }
}
