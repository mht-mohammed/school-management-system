<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;  


return new class extends Migration
{
    public function up(): void
    {
        $groups = DB::table('subjects')
            ->whereNotNull('grade_level_id')
            ->select('grade_level_id', 'name')
            ->groupBy('grade_level_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $dups = DB::table('subjects')
                ->where('grade_level_id', $group->grade_level_id)
                ->where('name', $group->name)
                ->orderBy('id')
                ->pluck('id');

            $keep = $dups->shift();

            foreach ($dups as $dupId) {
                DB::table('grades')->where('subject_id', $dupId)->update(['subject_id' => $keep]);
                DB::table('subjects')->where('id', $dupId)->delete();
            }
        }
    }

    public function down(): void
    {
        // Non-reversible; data is deduplicated
    }
};
