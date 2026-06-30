<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    protected $fillable = [
        'section_id',
        'teacher_id',
        'title',
        'description',
        'file_path',
        'link',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'section_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
