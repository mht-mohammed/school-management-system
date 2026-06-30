<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;  

return new class extends Migration
{
    public function up(): void
    {
        // Set fewer periods for الدراسات الاجتماعية and تكنولوجيا الحاسوب
        DB::table('subjects')
            ->where('name', 'like', '%الدراسات الاجتماعية%')
            ->orWhere('name', 'like', '%التكنولوجيا%')
            ->orWhere('name', 'like', '%الحاسوب%')
            ->update(['periods_per_week' => 2]);
    }

    public function down(): void
    {
        // Reset them to default 3
        DB::table('subjects')
            ->where('name', 'like', '%الدراسات الاجتماعية%')
            ->orWhere('name', 'like', '%التكنولوجيا%')
            ->orWhere('name', 'like', '%الحاسوب%')
            ->update(['periods_per_week' => 3]);
    }
};
