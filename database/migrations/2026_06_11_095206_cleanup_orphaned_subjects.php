<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;  


return new class extends Migration
{
    public function up(): void
    {
        // Delete orphaned schedules referencing orphan subjects
        DB::table('schedules')
            ->whereIn('subject_id', function ($q) {
                $q->select('id')->from('subjects')->whereNull('grade_level_id');
            })->delete();

        // Delete orphaned subjects with no grade_level_id
        DB::table('subjects')->whereNull('grade_level_id')->delete();
    }

    public function down(): void
    {
        // Non-reversible
    }
};
