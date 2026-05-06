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
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('brief_id')->nullable()->constrained('briefs')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, active, completed
            $table->integer('timer_minutes')->default(20);
            $table->integer('passing_score')->default(75);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_session_id')->constrained('quiz_sessions')->cascadeOnDelete();
            $table->string('type')->default('multiple_choice');
            $table->text('content');
            $table->text('correct_answer')->nullable();
            $table->json('context_data')->nullable();
            $table->integer('points')->default(10);
            $table->timestamps();
        });

        Schema::create('student_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('response_text')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->boolean('is_correct')->default(false);
            $table->text('ai_feedback')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_responses');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quiz_sessions');
    }
};
