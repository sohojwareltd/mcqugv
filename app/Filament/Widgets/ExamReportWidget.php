<?php

namespace App\Filament\Widgets;

use App\Models\Exam;
use App\Models\Participant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExamReportWidget extends BaseWidget
{
    protected static ?string $heading = 'Exams Report';

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Exam::query()
                    ->withCount(['participants', 'questions', 'categoryRules'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Exam')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label('Participants')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Questions')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('categoryRules_count')
                    ->label('Category Rules')
                    ->badge()
                    ->color('warning'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
