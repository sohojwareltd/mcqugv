<?php

namespace App\Filament\Resources\UniqueParticipantResource\Pages;

use App\Filament\Resources\UniqueParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUniqueParticipants extends ListRecords
{
    protected static string $resource = UniqueParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions needed - this is a read-only view of unique participants
        ];
    }
}
