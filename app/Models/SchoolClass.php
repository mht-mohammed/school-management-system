<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $fillable = [
        'name', 'grade_level_id', 'section', 'stage', 'teacher_id', 'academic_year',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function sections()
    {
        return $this->hasMany(SchoolClass::class, 'grade_level_id', 'grade_level_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'grade_level_id', 'grade_level_id');
    }
}
