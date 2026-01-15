<?php

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewParticipant extends ViewRecord
{
    protected static string $resource = ParticipantResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Eager load relationships for better performance
        $this->record->load([
            'participantQuestions.question.options',
            'participantQuestions.question.category',
            'answers.option',
        ]);

        return $data;
    }
}
