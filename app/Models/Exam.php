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

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function canBeActivated(): bool
    {
        $requiredCount = $this->categoryRules->sum('question_count');
        $availableCount = $this->questions()
            ->where('is_active', true)
            ->get()
            ->groupBy('category_id')
            ->map(function ($questions, $categoryId) {
                $rule = $this->categoryRules->firstWhere('category_id', $categoryId);
                return min($questions->count(), $rule ? $rule->question_count : 0);
            })
            ->sum();

        return $availableCount >= $requiredCount;
    }
}
