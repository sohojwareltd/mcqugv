<?php

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use App\Models\Participant;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListParticipants extends ListRecords
{
    protected static string $resource = ParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        
        // Only show participants from active exams
        $query->whereHas('exam', function ($q) {
            $q->where('is_active', true);
        });
        
        // If no participants have rank, sort by created_at desc instead
        if (!Participant::whereNotNull('rank')->exists()) {
            return $query->orderBy('created_at', 'desc');
        }
        
        return $query->orderBy('rank', 'asc')->orderBy('merit_position', 'asc');
    }
}
