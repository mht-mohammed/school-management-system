<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stage')->nullable();
            $table->string('academic_year');
            $table->timestamps();
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('grade_level_id')->nullable()->constrained()->nullOnDelete();
            $table->string('section', 10)->default('أ');
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['grade_level_id']);
            $table->dropColumn(['grade_level_id', 'section']);
        });
        Schema::dropIfExists('grade_levels');
    }
};
