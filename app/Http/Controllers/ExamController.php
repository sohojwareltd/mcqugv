<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Participant;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function home(): Response
    {
        $now = now();
        
        // Get current active exam (within active hours: start_time <= now <= end_time)
        $currentExam = Exam::where('is_active', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        // Get next upcoming exam (start_time > now)
        $nextExam = Exam::where('is_active', true)
            ->where('start_time', '>', $now)
            ->orderBy('start_time', 'asc')
            ->first();

        // Get most recent ended exam (end_time < now)
        $endedExam = Exam::where('is_active', true)
            ->where('end_time', '<', $now)
            ->orderBy('end_time', 'desc')
            ->first();

        // Determine which exam to show and its status
        // Priority: current exam > ended exam (with next exam info) > next exam
        $examToShow = null;
        $examStatus = null;
        $examData = null;
        $participantCount = 0;

        if ($currentExam) {
            // Show current active exam
            $examToShow = $currentExam;
            $examStatus = 'active';
            $participantCount = Participant::where('exam_id', $currentExam->id)->whereNotNull('completed_at')->count();
        } elseif ($endedExam) {
            // Show most recent ended exam (prioritize showing ended exam over next exam)
            $examToShow = $endedExam;
            $examStatus = 'ended';
            $participantCount = Participant::where('exam_id', $endedExam->id)->whereNotNull('completed_at')->count();
        } elseif ($nextExam) {
            // Show next upcoming exam if no ended exam
            $examToShow = $nextExam;
            $examStatus = 'upcoming';
            $participantCount = 0;
        }

        if ($examToShow) {
            $examData = [
                'id' => $examToShow->id,
                'title' => $examToShow->title,
                'total_questions' => $examToShow->total_questions,
                'start_time' => $examToShow->start_time?->toIso8601String(),
                'end_time' => $examToShow->end_time?->toIso8601String(),
                'result_publish_at' => $examToShow->result_publish_at?->toIso8601String(),
                'status' => $examStatus,
            ];

            // Add next exam info if current exam is ended and next exam exists
            if ($examStatus === 'ended' && $nextExam) {
                $examData['next_exam'] = [
                    'id' => $nextExam->id,
                    'title' => $nextExam->title,
                    'start_time' => $nextExam->start_time?->toIso8601String(),
                ];
            }
        }

        // Get the most recent completed exam (previous exam) for leaderboard
        $previousExam = Exam::whereHas('participants', function ($query) {
                $query->whereNotNull('completed_at');
            })
            ->withCount('participants')
            ->orderBy('end_time', 'desc')
            ->first();

        $previousLeaderboard = null;
        if ($previousExam) {
            $participants = Participant::where('exam_id', $previousExam->id)
                ->whereNotNull('completed_at')
                ->when(
                    Participant::where('exam_id', $previousExam->id)->whereNotNull('rank')->exists(),
                    fn ($query) => $query->orderBy('rank', 'asc'),
                    fn ($query) => $query->orderBy('score', 'desc')->orderBy('completed_at', 'asc')
                )
                ->limit(10) // Top 10 for display
                ->get()
                ->map(function ($participant, $index) {
                    return [
                        'rank' => $participant->rank ?? ($index + 1),
                        'full_name' => $participant->full_name,
                        'hsc_roll' => $participant->hsc_roll,
                    ];
                });

            $previousLeaderboard = [
                'exam' => [
                    'id' => $previousExam->id,
                    'title' => $previousExam->title,
                    'result_publish_at' => $previousExam->result_publish_at?->toIso8601String(),
                ],
                'participants' => $participants,
            ];
        }

        return Inertia::render('Home', [
            'exam' => $examData,
            'participantCount' => ($participantCount ?? 0) + 12430, // Base count for display
            'previousLeaderboard' => $previousLeaderboard,
        ]);
    }

    public function form(): Response
    {
        $now = now();
        
        // Get current active exam (within active hours)
        $activeExam = Exam::where('is_active', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        if (!$activeExam) {
            // Redirect to home if no active exam
            return redirect()->route('home');
        }

        return Inertia::render('ExamForm', [
            'exam' => [
                'id' => $activeExam->id,
                'title' => $activeExam->title,
            ],
        ]);
    }

    public function rules(string $token): Response
    {
        $participant = Participant::where('attempt_token', $token)->firstOrFail();

        // If exam is already completed, redirect to leaderboard
        if ($participant->isCompleted()) {
            return redirect()->route('leaderboard');
        }

        $exam = $participant->exam;
        $categoryRules = $exam->categoryRules()
            ->with('category')
            ->get()
            ->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'category' => [
                        'id' => $rule->category->id,
                        'name' => $rule->category->name,
                    ],
                    'question_count' => $rule->question_count,
                ];
            });

        return Inertia::render('ExamRules', [
            'token' => $token,
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'total_questions' => $exam->total_questions,
                'start_time' => $exam->start_time?->toIso8601String(),
                'end_time' => $exam->end_time?->toIso8601String(),
            ],
            'categoryRules' => $categoryRules,
        ]);
    }

    public function exam(string $token): Response
    {
        $participant = Participant::where('attempt_token', $token)->firstOrFail();

        // If exam is already completed, redirect to leaderboard
        if ($participant->isCompleted()) {
            return redirect()->route('leaderboard');
        }

        return Inertia::render('ExamScreen', ['token' => $token]);
    }

    public function complete(string $token): Response
    {
        $participant = Participant::where('attempt_token', $token)->firstOrFail();

        return Inertia::render('ExamComplete', [
            'token' => $token,
            'resultPublishAt' => $participant->exam->result_publish_at?->toIso8601String(),
        ]);
    }

    public function leaderboard(): Response
    {
        $now = now();
        
        // Get current active exam (within active hours)
        $currentExam = Exam::where('is_active', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        // Get next upcoming exam
        $nextExam = Exam::where('is_active', true)
            ->where('start_time', '>', $now)
            ->orderBy('start_time', 'asc')
            ->first();

        // Get all previous exams that have ENDED and have published results (result_publish_at <= now or null)
        // Only show exams that have ended (end_time < now) to prevent showing leaderboard before exam ends
        $previousExams = Exam::whereHas('participants', function ($query) {
                $query->whereNotNull('completed_at');
            })
            ->where('end_time', '<', $now) // Only show exams that have ended
            ->where(function ($query) use ($now) {
                $query->whereNull('result_publish_at')
                    ->orWhere('result_publish_at', '<=', $now);
            })
            ->orderBy('end_time', 'desc')
            ->get()
            ->map(function ($exam) {
                $participants = Participant::where('exam_id', $exam->id)
                    ->whereNotNull('completed_at')
                    ->when(
                        Participant::where('exam_id', $exam->id)->whereNotNull('rank')->exists(),
                        fn ($query) => $query->orderBy('rank', 'asc'),
                        fn ($query) => $query->orderBy('score', 'desc')->orderBy('completed_at', 'asc')
                    )
                    ->get()
                    ->map(function ($participant, $index) {
                        return [
                            'rank' => $participant->rank ?? ($index + 1),
                            'merit_position' => $participant->merit_position ?? ($index + 1),
                            'full_name' => $participant->full_name,
                            'hsc_roll' => $participant->hsc_roll,
                            'score' => $participant->score,
                            'completed_at' => $participant->completed_at->toIso8601String(),
                        ];
                    });

                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'end_time' => $exam->end_time?->toIso8601String(),
                    'result_publish_at' => $exam->result_publish_at?->toIso8601String(),
                    'participants' => $participants,
                ];
            });

        return Inertia::render('Leaderboard', [
            'currentExam' => $currentExam ? [
                'id' => $currentExam->id,
                'title' => $currentExam->title,
                'result_publish_at' => $currentExam->result_publish_at?->toIso8601String(),
            ] : null,
            'nextExam' => $nextExam ? [
                'id' => $nextExam->id,
                'title' => $nextExam->title,
                'start_time' => $nextExam->start_time?->toIso8601String(),
            ] : null,
            'previousExams' => $previousExams,
        ]);
    }
}
