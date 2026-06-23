<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;  


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('grade_level_id')->nullable()->constrained('grade_levels')->nullOnDelete();
        });

        $subjects = DB::table('subjects')
            ->join('school_classes', 'subjects.class_id', '=', 'school_classes.id')
            ->whereNotNull('school_classes.grade_level_id')
            ->select('subjects.id', 'school_classes.grade_level_id')
            ->get();
        foreach ($subjects as $s) {
            DB::table('subjects')->where('id', $s->id)->update(['grade_level_id' => $s->grade_level_id]);
        }
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['grade_level_id']);
            $table->dropColumn('grade_level_id');
        });
    }
};
