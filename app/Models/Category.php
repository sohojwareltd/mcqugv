<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function examCategoryRules(): HasMany
    {
        return $this->hasMany(ExamCategoryRule::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
