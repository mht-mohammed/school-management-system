<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'school_classes';

    protected $fillable = [
        'name', 'grade_level_id', 'section', 'stage', 'teacher_id', 'academic_year',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'grade_level_id', 'grade_level_id');
    }

    // Alias for consistent naming
    public function getSectionNameAttribute()
    {
        return $this->section;
    }
}
