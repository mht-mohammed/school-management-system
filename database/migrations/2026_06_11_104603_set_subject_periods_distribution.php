<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'الرياضيات' => 4,
            'العلوم الحياتية' => 4,
            'اللغة العربية' => 4,
            'اللغة الإنجليزية' => 4,
            'التربية الإسلامية' => 4,
            'الدراسات الاجتماعية' => 3,
        ];

        foreach ($map as $name => $count) {
            DB::table('subjects')->where('name', $name)->update(['periods_per_week' => $count]);
        }

        // التكنولوجيا والحاسوب (different possible names)
        DB::table('subjects')
            ->where(function ($q) {
                $q->where('name', 'like', '%التكنولوجيا%')
                  ->orWhere('name', 'like', '%الحاسوب%');
            })
            ->update(['periods_per_week' => 2]);
    }

    public function down(): void
    {
        // Non-reversible
    }
};
