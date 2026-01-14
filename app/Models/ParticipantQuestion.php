<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantQuestion extends Model
{
    protected $fillable = [
        'participant_id',
        'question_id',
        'order_no',
    ];

    protected $casts = [
        'order_no' => 'integer',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
