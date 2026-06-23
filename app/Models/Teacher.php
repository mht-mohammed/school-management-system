<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id', 'qualification', 'specialization', 'hire_date', 'salary', 'grade_distribution',
    ];

    protected $casts = [
        'grade_distribution' => 'array',
    ];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'teacher_id', 'class_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }

    public function taughtSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'user_id', 'subject_id')->withTimestamps();
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }
}
