<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileChangeRequest extends Model
{
    protected $fillable = [
        'user_id', 'role', 'changes', 'status', 'admin_note', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }
}
