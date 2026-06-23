<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;  

return new class extends Migration
{
    public function up(): void
    {
        // Truncate existing schedules (old format with start/end times)
        DB::table('schedules')->truncate();

        Schema::table('schedules', function (Blueprint $table) {
            $table->tinyInteger('period_number')->after('day_of_week');
            $table->dropColumn(['start_time', 'end_time']);
            $table->unique(['section_id', 'day_of_week', 'period_number'], 'sched_section_day_period_unique');
        });

        // Handle any schedules where section_id is null (set to class_id)
        DB::table('schedules')->whereNull('section_id')->update(['section_id' => DB::raw('class_id')]);
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique('sched_section_day_period_unique');
            $table->dropColumn('period_number');
            $table->time('start_time');
            $table->time('end_time');
        });
    }
};
