<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('school_phone', 50)->nullable()->after('school_logo');
            $table->string('school_email', 100)->nullable()->after('school_phone');
            $table->text('school_address')->nullable()->after('school_email');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['school_phone', 'school_email', 'school_address']);
        });
    }
};
