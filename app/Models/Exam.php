<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'start_time',
        'end_time',
        'total_questions',
        'result_publish_at',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_questions' => 'integer',
        'result_publish_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function categoryRules(): HasMany
    {
        return $this->hasMany(ExamCategoryRule::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function canBeActivated(): bool
    {
        $requiredCount = $this->categoryRules->sum('question_count');
        $availableCount = $this->categoryRules->sum(function ($rule) {
            $categoryQuestions = \App\Models\Question::where('category_id', $rule->category_id)
                ->where('is_active', true)
                ->count();
            return min($categoryQuestions, $rule->question_count);
        });

        return $availableCount >= $requiredCount;
    }
}
