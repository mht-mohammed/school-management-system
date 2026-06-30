<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'section_id',
        'teacher_id',
        'title',
        'description',
        'time_limit',
        'max_attempts',
        'scheduled_at',
        'scheduled_end',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'scheduled_end' => 'datetime',
            'time_limit' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'section_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
