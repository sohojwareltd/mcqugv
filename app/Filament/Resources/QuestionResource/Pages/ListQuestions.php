<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionResource\Actions\ImportQuestionsAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportQuestionsAction::make(),
            Actions\CreateAction::make(),
        ];
    }
}
