<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = [
        'exam_id',
        'full_name',
        'phone',
        'group',
        'hsc_roll',
        'hsc_passing_year',
        'board',
        'college',
        'attempt_token',
        'started_at',
        'completed_at',
        'score',
        'rank',
        'merit_position',
        'ip_address',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
        'rank' => 'integer',
        'merit_position' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function participantQuestions(): HasMany
    {
        return $this->hasMany(ParticipantQuestion::class)->orderBy('order_no');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }
}
