<?php

namespace App\Filament\Resources\QuestionResource\Actions;

use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportQuestionsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'importQuestions';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Import Questions from Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->form([
                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::where('is_active', true)->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Select the category for these questions'),

                FileUpload::make('file')
                    ->label('Excel File')
                    ->required()
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                    ->maxSize(10240) // 10MB
                    ->helperText('Upload an Excel file (.xlsx or .xls) with columns: Question, A, B, C, D, Ans')
                    ->downloadable()
                    ->disk('local')
                    ->directory('imports')
                    ->visibility('private'),
            ])
            ->action(function (array $data) {
                try {
                    // Get the file path from Filament FileUpload
                    // FileUpload returns the path relative to the disk root
                    $filePath = $data['file'] ?? null;
                    
                    if (empty($filePath)) {
                        throw new \Exception('No file uploaded. Please select a file.');
                    }

                    // Handle array format (if Filament returns array)
                    if (is_array($filePath)) {
                        $filePath = $filePath[0] ?? null;
                        if (empty($filePath)) {
                            throw new \Exception('Invalid file path format.');
                        }
                    }

                    // Ensure we have a string path
                    $filePath = (string) $filePath;

                    // Remove leading slash if present
                    $filePath = ltrim($filePath, '/');

                    // Use Storage facade to check and get the file
                    $disk = Storage::disk('local');
                    
                    // Ensure imports directory exists
                    if (!$disk->exists('imports')) {
                        $disk->makeDirectory('imports');
                    }
                    
                    // Try different possible paths
                    $possiblePaths = [
                        $filePath,
                        'imports/' . basename($filePath),
                        'imports/' . $filePath,
                        str_replace('imports/', '', $filePath),
                        ltrim($filePath, 'imports/'),
                    ];

                    $foundPath = null;
                    foreach ($possiblePaths as $path) {
                        if ($disk->exists($path)) {
                            $foundPath = $path;
                            break;
                        }
                    }

                    if (!$foundPath) {
                        // Log for debugging
                        \Log::error('File not found', [
                            'original_path' => $filePath,
                            'possible_paths' => $possiblePaths,
                            'disk_root' => $disk->path(''),
                            'all_files' => $disk->allFiles('imports'),
                        ]);
                        throw new \Exception('File not found. Please upload the file again. Original path: ' . $filePath);
                    }

                    // Get the full file path
                    $fullPath = $disk->path($foundPath);
                    
                    if (!file_exists($fullPath)) {
                        throw new \Exception('File not found at path: ' . $fullPath);
                    }

                    // Read Excel file
                    $rows = Excel::toArray([], $fullPath);
                    
                    if (empty($rows) || empty($rows[0])) {
                        throw new \Exception('Excel file is empty or invalid.');
                    }

                    $dataRows = $rows[0];
                    
                    // Skip header row (first row)
                    $headerRow = array_shift($dataRows);
                    
                    // Validate header row
                    $expectedHeaders = ['Question', 'A', 'B', 'C', 'D', 'Ans'];
                    $headerRowLower = array_map('strtolower', array_map('trim', $headerRow));
                    $expectedHeadersLower = array_map('strtolower', $expectedHeaders);
                    
                    // Check if headers match (case-insensitive)
                    $headerMatch = true;
                    foreach ($expectedHeadersLower as $index => $expectedHeader) {
                        if (!isset($headerRowLower[$index]) || $headerRowLower[$index] !== $expectedHeader) {
                            $headerMatch = false;
                            break;
                        }
                    }
                    
                    if (!$headerMatch) {
                        throw new \Exception('Invalid Excel format. Expected headers: Question, A, B, C, D, Ans');
                    }

                    $imported = 0;
                    $skipped = 0;
                    $errors = [];

                    DB::beginTransaction();

                    try {
                        foreach ($dataRows as $rowIndex => $row) {
                            $rowNumber = $rowIndex + 2; // +2 because we removed header and arrays are 0-indexed
                            
                            // Skip empty rows
                            if (empty(array_filter($row))) {
                                continue;
                            }

                            // Extract data (handle case-insensitive column matching)
                            $questionText = trim($row[0] ?? '');
                            $optionA = trim($row[1] ?? '');
                            $optionB = trim($row[2] ?? '');
                            $optionC = trim($row[3] ?? '');
                            $optionD = trim($row[4] ?? '');
                            $answer = strtoupper(trim($row[5] ?? ''));

                            // Validate required fields
                            if (empty($questionText)) {
                                $skipped++;
                                $errors[] = "Row {$rowNumber}: Question text is empty";
                                continue;
                            }

                            if (empty($optionA) || empty($optionB) || empty($optionC) || empty($optionD)) {
                                $skipped++;
                                $errors[] = "Row {$rowNumber}: One or more options are empty";
                                continue;
                            }

                            // Validate answer (must be A, B, C, or D)
                            if (!in_array($answer, ['A', 'B', 'C', 'D'])) {
                                $skipped++;
                                $errors[] = "Row {$rowNumber}: Invalid answer '{$answer}'. Must be A, B, C, or D";
                                continue;
                            }

                            // Create question (no exam_id - questions are randomized based on ExamCategoryRule)
                            $question = Question::create([
                                'category_id' => $data['category_id'],
                                'question_text' => $questionText,
                                'is_active' => true,
                            ]);

                            // Create options
                            $options = [
                                ['option_text' => $optionA, 'is_correct' => $answer === 'A'],
                                ['option_text' => $optionB, 'is_correct' => $answer === 'B'],
                                ['option_text' => $optionC, 'is_correct' => $answer === 'C'],
                                ['option_text' => $optionD, 'is_correct' => $answer === 'D'],
                            ];

                            foreach ($options as $optionData) {
                                Option::create([
                                    'question_id' => $question->id,
                                    'option_text' => $optionData['option_text'],
                                    'is_correct' => $optionData['is_correct'],
                                ]);
                            }

                            $imported++;
                        }

                        DB::commit();

                        // Clean up uploaded file using Storage
                        if ($foundPath && Storage::disk('local')->exists($foundPath)) {
                            Storage::disk('local')->delete($foundPath);
                        }

                        $message = "Successfully imported {$imported} question(s).";
                        if ($skipped > 0) {
                            $message .= " {$skipped} row(s) skipped.";
                        }

                        Notification::make()
                            ->title('Import Successful')
                            ->body($message)
                            ->success()
                            ->send();

                        if (!empty($errors)) {
                            \Log::warning('Question import errors', ['errors' => $errors]);
                        }

                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
