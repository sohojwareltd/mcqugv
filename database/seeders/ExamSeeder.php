<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Exam;
use App\Models\ExamCategoryRule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 exams starting from January 15, 2026
        $startDate = Carbon::create(2026, 1, 15, 10, 0, 0); // Jan 15, 2026 at 10:00 AM
        
        $categories = Category::all();
        $questionCounts = [
            'math' => 4,
            'bangla' => 4,
            'english' => 4,
            'ict' => 4,
            'general-knowledge' => 4,
        ];
        
        $totalQuestions = array_sum($questionCounts); // 50 questions total

        for ($i = 0; $i < 10; $i++) {
            $examDate = $startDate->copy()->addDays($i);
            $examNumber = $i + 1;
            
            $exam = Exam::firstOrCreate(
                ['title' => "UGV Admission Fair MCQ Exam - Day {$examNumber} ({$examDate->format('M d, Y')})"],
                [
                    'title' => "UGV Admission Fair MCQ Exam - Day {$examNumber} ({$examDate->format('M d, Y')})",
                    'start_time' => $examDate->copy()->setTime(10, 0, 0), // 10:00 AM
                    'end_time' => $examDate->copy()->setTime(23, 0, 0),   // 11:00 PM
                    'total_questions' => $totalQuestions,
                    'result_publish_at' => $examDate->copy()->setTime(23, 59, 0), // 11:59 PM
                    'is_active' => $i === 0, // Only first exam is active
                ]
            );

            // Create category rules for each exam
            foreach ($categories as $category) {
                $count = $questionCounts[$category->slug] ?? 0;
                if ($count > 0) {
                    ExamCategoryRule::firstOrCreate(
                        [
                            'exam_id' => $exam->id,
                            'category_id' => $category->id,
                        ],
                        [
                            'exam_id' => $exam->id,
                            'category_id' => $category->id,
                            'question_count' => $count,
                        ]
                    );
                }
            }
        }
    }
}
