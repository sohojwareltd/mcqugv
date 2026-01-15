<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamCategoryRule;
use App\Models\Option;
use App\Models\Participant;
use App\Models\ParticipantQuestion;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'group' => 'nullable|string|max:100',
            'hsc_roll' => 'nullable|string|max:50',
            'board' => 'nullable|string|max:100',
            'college' => 'nullable|string|max:255',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $now = now();

        // Only allow access to exams that are currently active (within active hours)
        if (! $exam->is_active) {
            return response()->json(['error' => 'Exam is not active'], 403);
        }

        // Check if exam is within active hours (start_time <= now <= end_time)
        if ($exam->start_time && $exam->start_time->isFuture()) {
            return response()->json([
                'error' => 'Exam has not started yet',
                'start_time' => $exam->start_time->toIso8601String(),
            ], 403);
        }

        if ($exam->end_time && $exam->end_time->isPast()) {
            return response()->json([
                'error' => 'Exam has ended',
                'end_time' => $exam->end_time->toIso8601String(),
            ], 403);
        }

        // Check if phone already exists for this exam
        $existingParticipant = Participant::where('exam_id', $exam->id)
            ->where('phone', $request->phone)
            ->first();

        if ($existingParticipant) {
            return response()->json([
                'error' => 'You have already participated in this exam',
                'token' => $existingParticipant->attempt_token,
                'completed' => $existingParticipant->isCompleted(),
            ], 409);
        }

        // Create participant
        $participant = Participant::create([
            'exam_id' => $exam->id,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'group' => $request->group,
            'hsc_roll' => $request->hsc_roll,
            'board' => $request->board,
            'college' => $request->college,
            'attempt_token' => Str::random(64),
            'started_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        // Generate random question paper
        $categoryRules = ExamCategoryRule::where('exam_id', $exam->id)->get();
        $selectedQuestions = collect();

        foreach ($categoryRules as $rule) {
            // Questions are randomized from category based on ExamCategoryRule (no exam_id needed)
            $questions = Question::where('category_id', $rule->category_id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit($rule->question_count)
                ->get();

            $selectedQuestions = $selectedQuestions->merge($questions);
        }

        // Shuffle all questions
        $shuffledQuestions = $selectedQuestions->shuffle();

        // Store participant questions
        foreach ($shuffledQuestions as $index => $question) {
            ParticipantQuestion::create([
                'participant_id' => $participant->id,
                'question_id' => $question->id,
                'order_no' => $index + 1,
            ]);
        }

        return response()->json([
            'token' => $participant->attempt_token,
            'total_questions' => $shuffledQuestions->count(),
        ]);
    }

    public function getQuestion(string $token): JsonResponse
    {
        $participant = Participant::where('attempt_token', $token)->firstOrFail();

        if ($participant->isCompleted()) {
            return response()->json(['error' => 'Exam already completed'], 403);
        }

        $answeredQuestionIds = Answer::where('participant_id', $participant->id)
            ->pluck('question_id')
            ->toArray();

        $currentQuestion = $participant->participantQuestions()
            ->with(['question.options' => function ($query) {
                $query->orderBy('id');
            }, 'question.category'])
            ->whereNotIn('question_id', $answeredQuestionIds)
            ->orderBy('order_no')
            ->first();

        if (! $currentQuestion) {
            return response()->json(['error' => 'No more questions'], 404);
        }

        $totalQuestions = $participant->participantQuestions()->count();
        $answeredCount = count($answeredQuestionIds);
        $currentQuestionNumber = $currentQuestion->order_no;

        return response()->json([
            'question' => [
                'id' => $currentQuestion->question->id,
                'text' => $currentQuestion->question->question_text,
                'category' => $currentQuestion->question->category->name,
                'options' => $currentQuestion->question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'text' => $option->option_text,
                    ];
                }),
            ],
            'progress' => [
                'current' => $currentQuestionNumber,
                'total' => $totalQuestions,
                'answered' => $answeredCount,
            ],
        ]);
    }

    public function submitAnswer(Request $request, string $token): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_id' => 'required|exists:options,id',
        ]);

        $participant = Participant::where('attempt_token', $token)->firstOrFail();

        if ($participant->isCompleted()) {
            return response()->json(['error' => 'Exam already completed'], 403);
        }

        // Check if question belongs to participant's paper
        $participantQuestion = ParticipantQuestion::where('participant_id', $participant->id)
            ->where('question_id', $request->question_id)
            ->firstOrFail();

        // Check if already answered
        $existingAnswer = Answer::where('participant_id', $participant->id)
            ->where('question_id', $request->question_id)
            ->first();

        if ($existingAnswer) {
            // Update existing answer
            $option = Option::findOrFail($request->option_id);
            $existingAnswer->update([
                'option_id' => $request->option_id,
                'is_correct' => $option->is_correct,
            ]);
        } else {
            // Create new answer
            $option = Option::findOrFail($request->option_id);
            Answer::create([
                'participant_id' => $participant->id,
                'question_id' => $request->question_id,
                'option_id' => $request->option_id,
                'is_correct' => $option->is_correct,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function finish(string $token): JsonResponse
    {
        $participant = Participant::where('attempt_token', $token)->firstOrFail();

        if ($participant->isCompleted()) {
            return response()->json(['error' => 'Exam already completed'], 403);
        }

        // Calculate score
        $score = Answer::where('participant_id', $participant->id)
            ->where('is_correct', true)
            ->count();

        $participant->update([
            'score' => $score,
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'score' => $score,
            'total_questions' => $participant->participantQuestions()->count(),
            'result_publish_at' => $participant->exam->result_publish_at?->toIso8601String(),
        ]);
    }

    public function leaderboard(Exam $exam): JsonResponse
    {
        $now = now();

        if ($exam->result_publish_at && $exam->result_publish_at->isFuture()) {
            return response()->json([
                'error' => 'Results are not published yet',
                'publish_at' => $exam->result_publish_at->toIso8601String(),
            ], 403);
        }

        // Use stored rank if available, otherwise calculate on the fly
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
                    'phone' => $participant->phone,
                    'score' => $participant->score,
                    'completed_at' => $participant->completed_at->toIso8601String(),
                ];
            });

        return response()->json([
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
            ],
            'participants' => $participants,
        ]);
    }
}
