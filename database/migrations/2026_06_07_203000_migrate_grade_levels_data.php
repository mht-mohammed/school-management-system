<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create grade_levels from existing school_classes names
        $classes = DB::table('school_classes')->get();
        $created = [];

        foreach ($classes as $class) {
            $name = $class->name;
            if (!isset($created[$name])) {
                $id = DB::table('grade_levels')->insertGetId([
                    'name' => $name,
                    'stage' => $class->stage,
                    'academic_year' => $class->academic_year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created[$name] = $id;
            }
        }

        // 2. Link sections to grade_levels
        foreach ($classes as $class) {
            DB::table('school_classes')
                ->where('id', $class->id)
                ->update([
                    'grade_level_id' => $created[$class->name],
                    'section' => 'أ',
                ]);
        }

        // 3. Link subjects to grade_levels via their class
        DB::statement('UPDATE subjects s JOIN school_classes c ON s.class_id = c.id SET s.grade_level_id = c.grade_level_id');
    }

    public function down(): void
    {
        DB::table('grade_levels')->truncate();
        DB::table('school_classes')->update(['grade_level_id' => null, 'section' => 'أ']);
        DB::table('subjects')->update(['grade_level_id' => null]);
    }
};
