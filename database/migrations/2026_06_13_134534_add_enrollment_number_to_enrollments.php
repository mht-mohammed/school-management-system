<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->bigInteger('enrollment_number')->nullable()->after('id');
        });

        // Populate existing rows with sequential numbers
        $rows = DB::table('enrollments')->orderBy('id')->get();
        $num = 1;
        foreach ($rows as $row) {
            DB::table('enrollments')->where('id', $row->id)->update(['enrollment_number' => $num++]);
        }
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('enrollment_number');
        });
    }
};
