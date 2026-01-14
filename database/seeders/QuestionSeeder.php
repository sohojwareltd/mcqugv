<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Get all exams (including the 10 new ones)
        $exams = Exam::all();
        
        if ($exams->isEmpty()) {
            $this->command->warn('No exams found. Please run ExamSeeder first.');
            return;
        }

        $questions = [
            // Math Questions
            'math' => [
                [
                    'text' => 'What is the value of 2 + 2?',
                    'options' => ['2', '3', '4', '5'],
                    'correct' => 2,
                ],
                [
                    'text' => 'What is the square root of 16?',
                    'options' => ['2', '3', '4', '5'],
                    'correct' => 2,
                ],
                [
                    'text' => 'What is 10 × 5?',
                    'options' => ['40', '45', '50', '55'],
                    'correct' => 2,
                ],
                [
                    'text' => 'What is 100 ÷ 4?',
                    'options' => ['20', '25', '30', '35'],
                    'correct' => 1,
                ],
                [
                    'text' => 'What is the value of π (pi) approximately?',
                    'options' => ['3.14', '3.41', '3.44', '3.24'],
                    'correct' => 0,
                ],
                [
                    'text' => 'What is 15 + 25?',
                    'options' => ['35', '40', '45', '50'],
                    'correct' => 1,
                ],
                [
                    'text' => 'What is 8²?',
                    'options' => ['56', '64', '72', '80'],
                    'correct' => 1,
                ],
            ],
            // Bangla Questions
            'bangla' => [
                [
                    'text' => 'বাংলা ভাষার বর্ণমালায় মোট কতটি বর্ণ আছে?',
                    'options' => ['৪৭', '৪৯', '৫০', '৫১'],
                    'correct' => 2,
                ],
                [
                    'text' => 'রবীন্দ্রনাথ ঠাকুর কোন সালে নোবেল পুরস্কার পান?',
                    'options' => ['১৯১২', '১৯১৩', '১৯১৪', '১৯১৫'],
                    'correct' => 1,
                ],
                [
                    'text' => 'কাজী নজরুল ইসলামের উপাধি কি?',
                    'options' => ['বিদ্রোহী কবি', 'কবিগুরু', 'রাজনীতিবিদ', 'শিক্ষাবিদ'],
                    'correct' => 0,
                ],
                [
                    'text' => 'বাংলাদেশের জাতীয় কবি কে?',
                    'options' => ['রবীন্দ্রনাথ ঠাকুর', 'কাজী নজরুল ইসলাম', 'জসিমউদ্দীন', 'সুকান্ত ভট্টাচার্য'],
                    'correct' => 1,
                ],
                [
                    'text' => 'বাংলাদেশের জাতীয় সঙ্গীতের রচয়িতা কে?',
                    'options' => ['কাজী নজরুল ইসলাম', 'রবীন্দ্রনাথ ঠাকুর', 'জসিমউদ্দীন', 'নজরুল ইসলাম'],
                    'correct' => 1,
                ],
                [
                    'text' => 'বাংলা নববর্ষ কখন?',
                    'options' => ['১লা জানুয়ারি', '১৪ই এপ্রিল', '১৫ই এপ্রিল', '১৬ই এপ্রিল'],
                    'correct' => 1,
                ],
                [
                    'text' => 'বাংলা ভাষায় কতটি স্বরবর্ণ আছে?',
                    'options' => ['৯', '১০', '১১', '১২'],
                    'correct' => 2,
                ],
            ],
            // English Questions
            'english' => [
                [
                    'text' => 'What is the plural of "child"?',
                    'options' => ['childs', 'children', 'childes', 'childies'],
                    'correct' => 1,
                ],
                [
                    'text' => 'Which word is a synonym for "happy"?',
                    'options' => ['sad', 'angry', 'joyful', 'tired'],
                    'correct' => 2,
                ],
                [
                    'text' => 'What is the past tense of "go"?',
                    'options' => ['goed', 'went', 'gone', 'going'],
                    'correct' => 1,
                ],
                [
                    'text' => 'Which sentence is correct?',
                    'options' => ['I am go to school', 'I go to school', 'I goes to school', 'I going to school'],
                    'correct' => 1,
                ],
                [
                    'text' => 'What is the capital letter of "a"?',
                    'options' => ['A', 'B', 'C', 'D'],
                    'correct' => 0,
                ],
                [
                    'text' => 'How many letters are in the English alphabet?',
                    'options' => ['24', '25', '26', '27'],
                    'correct' => 2,
                ],
                [
                    'text' => 'What is the opposite of "good"?',
                    'options' => ['better', 'best', 'bad', 'well'],
                    'correct' => 2,
                ],
            ],
            // ICT Questions
            'ict' => [
                [
                    'text' => 'What does CPU stand for?',
                    'options' => ['Central Processing Unit', 'Computer Personal Unit', 'Central Program Utility', 'Computer Processor Unit'],
                    'correct' => 0,
                ],
                [
                    'text' => 'What is the full form of HTML?',
                    'options' => ['HyperText Markup Language', 'HighText Machine Language', 'HyperText and links Markup Language', 'None of these'],
                    'correct' => 0,
                ],
                [
                    'text' => 'What does RAM stand for?',
                    'options' => ['Random Access Memory', 'Read Access Memory', 'Rapid Access Memory', 'Random Access Module'],
                    'correct' => 0,
                ],
                [
                    'text' => 'Which of the following is not a programming language?',
                    'options' => ['Python', 'Java', 'HTML', 'C++'],
                    'correct' => 2,
                ],
                [
                    'text' => 'What is the default extension of a Word document?',
                    'options' => ['.txt', '.doc', '.docx', '.pdf'],
                    'correct' => 2,
                ],
            ],
            // General Knowledge Questions
            'general-knowledge' => [
                [
                    'text' => 'What is the capital of Bangladesh?',
                    'options' => ['Chittagong', 'Dhaka', 'Sylhet', 'Rajshahi'],
                    'correct' => 1,
                ],
                [
                    'text' => 'How many districts are there in Bangladesh?',
                    'options' => ['62', '64', '66', '68'],
                    'correct' => 1,
                ],
                [
                    'text' => 'What is the largest ocean in the world?',
                    'options' => ['Atlantic Ocean', 'Indian Ocean', 'Arctic Ocean', 'Pacific Ocean'],
                    'correct' => 3,
                ],
                [
                    'text' => 'Which is the largest continent?',
                    'options' => ['Africa', 'Asia', 'Europe', 'North America'],
                    'correct' => 1,
                ],
                [
                    'text' => 'How many sides does a triangle have?',
                    'options' => ['2', '3', '4', '5'],
                    'correct' => 1,
                ],
            ],
        ];

        // Generate more questions for each category to have enough for 10 exams
        $expandedQuestions = [];
        
        // Additional questions pool for each category
        $additionalQuestions = [
            'math' => [
                ['text' => 'What is 3² + 4²?', 'options' => ['9', '16', '25', '7'], 'correct' => 2],
                ['text' => 'What is 100 - 37?', 'options' => ['63', '67', '73', '77'], 'correct' => 0],
                ['text' => 'What is 12 × 8?', 'options' => ['84', '90', '96', '104'], 'correct' => 2],
                ['text' => 'What is 144 ÷ 12?', 'options' => ['10', '11', '12', '13'], 'correct' => 2],
                ['text' => 'What is 5³?', 'options' => ['100', '115', '125', '135'], 'correct' => 2],
                ['text' => 'What is the area of a square with side 5?', 'options' => ['20', '25', '30', '35'], 'correct' => 1],
                ['text' => 'What is 7 × 9?', 'options' => ['56', '63', '70', '77'], 'correct' => 1],
                ['text' => 'What is 200 ÷ 4?', 'options' => ['40', '45', '50', '55'], 'correct' => 2],
            ],
            'bangla' => [
                ['text' => 'বাংলাদেশের স্বাধীনতা দিবস কখন?', 'options' => ['২৬শে মার্চ', '১৬ই ডিসেম্বর', '২১শে ফেব্রুয়ারি', '১৪ই এপ্রিল'], 'correct' => 0],
                ['text' => 'বাংলাদেশের জাতীয় পাখি কি?', 'options' => ['কাক', 'দোয়েল', 'শালিক', 'চড়াই'], 'correct' => 1],
                ['text' => 'বাংলাদেশের জাতীয় ফুল কি?', 'options' => ['গোলাপ', 'শাপলা', 'জুঁই', 'কমল'], 'correct' => 1],
                ['text' => 'বাংলাদেশের জাতীয় ফল কি?', 'options' => ['আম', 'কাঁঠাল', 'লিচু', 'জাম'], 'correct' => 1],
                ['text' => 'বাংলাদেশের জাতীয় খেলা কি?', 'options' => ['ক্রিকেট', 'ফুটবল', 'কাবাডি', 'হকি'], 'correct' => 2],
                ['text' => 'বাংলাদেশের প্রথম রাষ্ট্রপতি কে?', 'options' => ['শেখ মুজিবুর রহমান', 'জিয়াউর রহমান', 'আবুল মনসুর আহমেদ', 'খন্দকার মোশতাক'], 'correct' => 0],
                ['text' => 'বাংলাদেশের জাতীয় কবি কাজী নজরুল ইসলামের জন্ম কবে?', 'options' => ['১৮৯৯', '১৮৯৮', '১৮৯৭', '১৮৯৬'], 'correct' => 0],
                ['text' => 'বাংলাদেশের সবচেয়ে বড় নদী কোনটি?', 'options' => ['পদ্মা', 'মেঘনা', 'যমুনা', 'ব্রহ্মপুত্র'], 'correct' => 0],
            ],
            'english' => [
                ['text' => 'What is the past tense of "eat"?', 'options' => ['eated', 'ate', 'eaten', 'eating'], 'correct' => 1],
                ['text' => 'Which word means "very big"?', 'options' => ['small', 'tiny', 'huge', 'little'], 'correct' => 2],
                ['text' => 'What is a group of lions called?', 'options' => ['herd', 'pack', 'pride', 'flock'], 'correct' => 2],
                ['text' => 'Which sentence uses correct grammar?', 'options' => ['He don\'t like it', 'He doesn\'t like it', 'He doesn\'t likes it', 'He don\'t likes it'], 'correct' => 1],
                ['text' => 'What is the comparative form of "good"?', 'options' => ['gooder', 'better', 'best', 'more good'], 'correct' => 1],
                ['text' => 'How many vowels are in the word "education"?', 'options' => ['3', '4', '5', '6'], 'correct' => 2],
                ['text' => 'What is the synonym of "begin"?', 'options' => ['end', 'start', 'finish', 'stop'], 'correct' => 1],
                ['text' => 'Which is a proper noun?', 'options' => ['city', 'Dhaka', 'country', 'river'], 'correct' => 1],
            ],
            'ict' => [
                ['text' => 'What does WWW stand for?', 'options' => ['World Wide Web', 'World Web Wide', 'Wide World Web', 'Web World Wide'], 'correct' => 0],
                ['text' => 'What is the smallest unit of data?', 'options' => ['Byte', 'Bit', 'Kilobyte', 'Megabyte'], 'correct' => 1],
                ['text' => 'Which key is used to delete text to the right?', 'options' => ['Backspace', 'Delete', 'Enter', 'Shift'], 'correct' => 1],
                ['text' => 'What does PDF stand for?', 'options' => ['Portable Document Format', 'Personal Document Format', 'Print Document Format', 'Public Document Format'], 'correct' => 0],
                ['text' => 'Which is not an operating system?', 'options' => ['Windows', 'Linux', 'Microsoft Word', 'macOS'], 'correct' => 2],
                ['text' => 'What is the shortcut to copy text?', 'options' => ['Ctrl+A', 'Ctrl+C', 'Ctrl+V', 'Ctrl+X'], 'correct' => 1],
                ['text' => 'What does USB stand for?', 'options' => ['Universal Serial Bus', 'United Serial Bus', 'Universal System Bus', 'United System Bus'], 'correct' => 0],
            ],
            'general-knowledge' => [
                ['text' => 'What is the currency of Bangladesh?', 'options' => ['Rupee', 'Taka', 'Dollar', 'Euro'], 'correct' => 1],
                ['text' => 'How many divisions are there in Bangladesh?', 'options' => ['6', '7', '8', '9'], 'correct' => 2],
                ['text' => 'What is the longest river in Bangladesh?', 'options' => ['Padma', 'Meghna', 'Jamuna', 'Brahmaputra'], 'correct' => 1],
                ['text' => 'Which is the smallest continent?', 'options' => ['Europe', 'Australia', 'Antarctica', 'South America'], 'correct' => 1],
                ['text' => 'What is the largest country in the world?', 'options' => ['China', 'USA', 'Russia', 'Canada'], 'correct' => 2],
            ],
        ];
        
        foreach ($questions as $categorySlug => $categoryQuestions) {
            $expandedQuestions[$categorySlug] = array_merge($categoryQuestions, $additionalQuestions[$categorySlug] ?? []);
        }

        // Create questions for each exam
        foreach ($exams as $exam) {
            foreach ($expandedQuestions as $categorySlug => $categoryQuestions) {
                $category = Category::where('slug', $categorySlug)->first();
                if (! $category) {
                    continue;
                }

                // Get the required count for this category
                $requiredCounts = [
                    'math' => 12,
                    'bangla' => 12,
                    'english' => 12,
                    'ict' => 8,
                    'general-knowledge' => 6,
                ];
                $required = $requiredCounts[$categorySlug] ?? 0;
                $questionsToCreate = array_slice($categoryQuestions, 0, $required);

                foreach ($questionsToCreate as $q) {
                    $question = Question::firstOrCreate(
                        [
                            'exam_id' => $exam->id,
                            'category_id' => $category->id,
                            'question_text' => $q['text'],
                        ],
                        [
                            'exam_id' => $exam->id,
                            'category_id' => $category->id,
                            'question_text' => $q['text'],
                            'is_active' => true,
                        ]
                    );

                    // Create options
                    $existingOptions = Option::where('question_id', $question->id)->count();
                    if ($existingOptions === 0) {
                        foreach ($q['options'] as $index => $optionText) {
                            Option::create([
                                'question_id' => $question->id,
                                'option_text' => $optionText,
                                'is_correct' => $index === $q['correct'],
                            ]);
                        }
                    }
                }
            }
        }
    }
}
