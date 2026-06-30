<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // إضافة حقول ولي الأمر
            $table->string('guardian_name')->nullable()->after('email');
            $table->string('guardian_email')->nullable()->after('guardian_name');
            $table->string('guardian_phone', 20)->nullable()->after('guardian_email');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['guardian_name', 'guardian_email', 'guardian_phone']);
        });
    }
};
