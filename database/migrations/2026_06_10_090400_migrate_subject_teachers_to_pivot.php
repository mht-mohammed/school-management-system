<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $subjects = DB::table('subjects')->whereNotNull('teacher_id')->get();
        foreach ($subjects as $subject) {
            $teacher = DB::table('teachers')->where('user_id', $subject->teacher_id)->first();
            if ($teacher) {
                DB::table('subject_teacher')->updateOrInsert(
                    ['subject_id' => $subject->id, 'user_id' => $subject->teacher_id],
                    ['created_at' => $subject->created_at ?? now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
