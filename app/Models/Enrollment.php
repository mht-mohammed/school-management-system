<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'enrollment_number', 'student_name', 'dob',
        'stage', 'status', 'notes',
        'guardian_name', 'guardian_email', 'guardian_phone',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
