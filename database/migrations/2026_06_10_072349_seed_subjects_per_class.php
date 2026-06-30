<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $subjectNames = ['الرياضيات', 'العلوم الحياتية', 'اللغة العربية', 'اللغة الإنجليزية', 'التربية الإسلامية', 'الدراسات الاجتماعية', 'التكنولوجيا والحاسوب'];
        $classes = DB::table('school_classes')->pluck('id');

        $now = now();
        $inserts = [];

        foreach ($classes as $classId) {
            foreach ($subjectNames as $name) {
                $inserts[] = [
                    'class_id' => $classId,
                    'name' => $name,
                    'teacher_id' => null,
                    'coefficient' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('subjects')->insert($inserts);
    }

    public function down(): void
    {
        // لا يمكن التراجع
    }
};
