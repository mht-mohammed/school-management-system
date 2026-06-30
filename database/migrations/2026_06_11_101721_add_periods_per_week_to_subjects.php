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
			$table->integer('periods_per_week')->default(3);
		});

		DB::table('subjects')->whereIn('name', ['الرياضيات', 'اللغة العربية', 'اللغة الإنجليزية'])->update(['periods_per_week' => 5]);
		DB::table('subjects')->whereIn('name', ['العلوم الحياتية'])->update(['periods_per_week' => 4]);
	}

	public function down(): void
	{
		Schema::table('subjects', function (Blueprint $table) {
			$table->dropColumn('periods_per_week');
		});
	}
};
