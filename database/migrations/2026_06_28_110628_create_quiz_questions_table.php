<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->text('question');
            $table->enum('type', ['mc', 'tf', 'text'])->default('mc');
            $table->json('options')->nullable()->comment('for MC: array of 4 options');
            $table->string('correct_answer')->nullable()->comment('MC: option index 0-3, TF: 1/0, text: model answer');
            $table->unsignedInteger('marks')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
