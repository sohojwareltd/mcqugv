<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentParticipantsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Participants';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Participant::query()
                    ->with('exam')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Exam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : 'N/A')
                    ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
                Tables\Columns\IconColumn::make('completed_at')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
