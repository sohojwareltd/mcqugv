<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Exam;
use App\Models\ExamCategoryRule;
use Illuminate\Database\Seeder;

class ScheduledExamSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $questionCounts = [
            'math' => 5,
            'bangla' => 5,
            'english' => 5,
            'ict' => 3,
            'general-knowledge' => 2,
        ];

        // Create 10 exams from today to next 10 days
        for ($i = 0; $i < 10; $i++) {
            $date = now()->addDays($i);
            $startTime = $date->copy()->setTime(9, 0, 0); // 9 AM
            $endTime = $date->copy()->setTime(18, 0, 0); // 6 PM
            $resultPublishAt = $date->copy()->addDay()->setTime(10, 0, 0); // Next day 10 AM

            $exam = Exam::create([
                'title' => 'Daily MCQ Exam - ' . $date->format('d M Y'),
                'total_questions' => 20,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'result_publish_at' => $resultPublishAt,
                'is_active' => $i === 0, // Only first exam is active
            ]);

            // Create category rules
            foreach ($categories as $category) {
                $count = $questionCounts[$category->slug] ?? 0;
                if ($count > 0) {
                    ExamCategoryRule::create([
                        'exam_id' => $exam->id,
                        'category_id' => $category->id,
                        'question_count' => $count,
                    ]);
                }
            }
        }
    }
}
