<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Exam;
use App\Models\Participant;
use Illuminate\Support\Collection;

class LeaderboardCalculationService
{
    /**
     * Category priority order for tie-breaking
     */
    private const CATEGORY_PRIORITY = [
        'math',
        'english',
        'bangla',
        'ict',
        'general-knowledge',
    ];

    /**
     * Calculate and update leaderboard ranks for an exam
     */
    public function calculateLeaderboard(Exam $exam): array
    {
        // Get all completed participants
        $participants = Participant::where('exam_id', $exam->id)
            ->whereNotNull('completed_at')
            ->with(['answers.option', 'participantQuestions.question.category'])
            ->get();

        if ($participants->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No completed participants found for this exam.',
            ];
        }

        // Calculate category scores for each participant
        $participantsWithScores = $participants->map(function ($participant) {
            return $this->calculateParticipantScores($participant);
        });

        // Sort participants using the ranking criteria
        $sorted = $this->sortParticipants($participantsWithScores);

        // Assign ranks and merit positions
        $currentRank = 1;
        $currentMeritPosition = 1;
        $previousParticipant = null;

        foreach ($sorted as $index => $participantData) {
            $participant = $participantData['participant'];
            
            // Check if this participant is tied with the previous one
            $isTied = $previousParticipant && $this->compareParticipants($previousParticipant, $participantData) === 0;
            
            // If not tied, update rank and merit position
            if (!$isTied) {
                $currentRank = $index + 1;
                $currentMeritPosition = $currentRank;
            }

            $participant->update([
                'rank' => $currentRank,
                'merit_position' => $currentMeritPosition,
            ]);
            
            $previousParticipant = $participantData;
        }

        return [
            'success' => true,
            'message' => "Leaderboard calculated successfully. {$sorted->count()} participants ranked.",
            'total_participants' => $sorted->count(),
        ];
    }

    /**
     * Calculate scores for a participant including category breakdowns
     */
    private function calculateParticipantScores(Participant $participant): array
    {
        // Get all correct answers with their questions and categories
        $correctAnswers = $participant->answers()
            ->where('is_correct', true)
            ->with(['question.category'])
            ->get();
        
        // Get category mapping (slug => id)
        $categoryMap = Category::pluck('id', 'slug')->toArray();
        
        // Calculate total score
        $totalScore = $correctAnswers->count();
        
        // Calculate category scores
        $categoryScores = [];
        foreach (self::CATEGORY_PRIORITY as $slug) {
            $categoryId = $categoryMap[$slug] ?? null;
            if ($categoryId) {
                $categoryScore = $correctAnswers->filter(function ($answer) use ($categoryId) {
                    return $answer->question && $answer->question->category_id == $categoryId;
                })->count();
                
                $categoryScores[$slug] = $categoryScore;
            } else {
                $categoryScores[$slug] = 0;
            }
        }

        // Calculate completion time in seconds
        $completionTime = $participant->started_at && $participant->completed_at
            ? $participant->started_at->diffInSeconds($participant->completed_at)
            : PHP_INT_MAX; // If no time data, put at end

        return [
            'participant' => $participant,
            'total_score' => $totalScore,
            'category_scores' => $categoryScores,
            'completion_time' => $completionTime,
        ];
    }

    /**
     * Sort participants according to ranking criteria
     */
    private function sortParticipants(Collection $participants): Collection
    {
        return $participants->sort(function ($a, $b) {
            return $this->compareParticipants($a, $b);
        })->values();
    }

    /**
     * Compare two participants for ranking
     * Returns: -1 if $a should rank higher, 1 if $b should rank higher, 0 if equal
     */
    private function compareParticipants(array $a, array $b): int
    {
        // Primary: Total score (descending - higher is better)
        if ($a['total_score'] !== $b['total_score']) {
            return $b['total_score'] <=> $a['total_score'];
        }

        // Secondary: Completion time (ascending - faster is better)
        if ($a['completion_time'] !== $b['completion_time']) {
            return $a['completion_time'] <=> $b['completion_time'];
        }

        // Tertiary: Category scores in priority order
        foreach (self::CATEGORY_PRIORITY as $categorySlug) {
            $scoreA = $a['category_scores'][$categorySlug] ?? 0;
            $scoreB = $b['category_scores'][$categorySlug] ?? 0;
            
            if ($scoreA !== $scoreB) {
                return $scoreB <=> $scoreA; // Higher score is better
            }
        }

        // If everything is equal, maintain original order (by ID)
        return $a['participant']->id <=> $b['participant']->id;
    }
}
