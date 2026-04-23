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
            $table->foreignId('brief_id')->constrained('briefs')->cascadeOnDelete();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, active, completed
            $table->integer('timer_minutes')->default(20);
            $table->integer('passing_score')->default(75);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_session_id')->constrained('quiz_sessions')->cascadeOnDelete();
            $table->enum('type', ['multiple_choice', 'code'])->default('multiple_choice');
            $table->text('content');
            $table->text('correct_answer')->nullable();
            $table->json('context_data')->nullable();
            $table->integer('points')->default(10);
            $table->timestamps();
        });

        Schema::create('student_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->text('response_text');
            $table->float('score')->default(0);
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
