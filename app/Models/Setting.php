<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'school_name', 'school_logo', 'elearning_url',
        'school_phone', 'school_email', 'school_address',
    ];

    public static function instance()
    {
        return static::firstOrCreate([], [
            'school_name' => 'الإبداع الحديثة',
        ]);
    }
}
