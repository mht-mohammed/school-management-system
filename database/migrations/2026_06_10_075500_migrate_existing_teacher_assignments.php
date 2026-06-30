<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $classes = DB::table('school_classes')->whereNotNull('teacher_id')->get();
        foreach ($classes as $class) {
            $teacher = DB::table('teachers')->where('user_id', $class->teacher_id)->first();
            if ($teacher) {
                DB::table('class_teacher')->updateOrInsert(
                    ['teacher_id' => $teacher->id, 'class_id' => $class->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
