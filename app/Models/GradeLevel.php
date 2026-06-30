<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $fillable = [
        'name', 'stage', 'academic_year',
    ];

    public function sections()
    {
        return $this->hasMany(SchoolClass::class, 'grade_level_id');
    }
}
