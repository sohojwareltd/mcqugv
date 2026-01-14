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
        $activeExam = Exam::where('is_active', true)->first();
        $participantCount = Participant::whereNotNull('completed_at')->count();

        // Get the most recent completed exam (previous exam) for leaderboard
        $previousExam = Exam::whereHas('participants', function ($query) {
                $query->whereNotNull('completed_at');
            })
            ->withCount('participants')
            ->orderBy('updated_at', 'desc')
            ->first();

        $previousLeaderboard = null;
        if ($previousExam) {
            $participants = Participant::where('exam_id', $previousExam->id)
                ->whereNotNull('completed_at')
                ->orderBy('score', 'desc')
                ->orderBy('completed_at', 'asc')
                ->limit(10) // Top 10 for display
                ->get()
                ->map(function ($participant, $index) {
                    return [
                        'rank' => $index + 1,
                        'full_name' => $participant->full_name,
                        'phone' => $participant->phone,
                        'score' => $participant->score,
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
            'exam' => $activeExam ? [
                'id' => $activeExam->id,
                'title' => $activeExam->title,
                'total_questions' => $activeExam->total_questions,
                'result_publish_at' => $activeExam->result_publish_at?->toIso8601String(),
            ] : null,
            'participantCount' => $participantCount + 12430, // Base count for display
            'previousLeaderboard' => $previousLeaderboard,
        ]);
    }

    public function form(): Response
    {
        return Inertia::render('ExamForm');
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
        $activeExam = Exam::where('is_active', true)->first();
        return Inertia::render('Leaderboard', [
            'examId' => $activeExam?->id ?? 1,
        ]);
    }
}
