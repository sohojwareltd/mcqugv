<?php

namespace App\Filament\Exports;

use App\Models\Participant;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ParticipantExportExporter extends Exporter
{
    protected static ?string $model = Participant::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('rank')
                ->label('Rank'),
            ExportColumn::make('merit_position')
                ->label('Merit Position'),
            ExportColumn::make('exam.title')
                ->label('Exam'),
            ExportColumn::make('full_name')
                ->label('Full Name'),
            ExportColumn::make('phone')
                ->label('Phone'),
            ExportColumn::make('hsc_roll')
                ->label('HSC Roll'),
            ExportColumn::make('group')
                ->label('Group'),
            ExportColumn::make('board')
                ->label('Board'),
            ExportColumn::make('college')
                ->label('College'),
            ExportColumn::make('score')
                ->label('Score'),
            ExportColumn::make('exam.start_time')
                ->label('Exam Start Time')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),
            ExportColumn::make('exam.end_time')
                ->label('Exam End Time')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),
            ExportColumn::make('started_at')
                ->label('Started At')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),
            ExportColumn::make('completed_at')
                ->label('Completed At')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),
            ExportColumn::make('ip_address')
                ->label('IP Address'),
            ExportColumn::make('created_at')
                ->label('Registered At')
                ->formatStateUsing(fn ($state) => $state->format('d/m/Y H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your participant export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
