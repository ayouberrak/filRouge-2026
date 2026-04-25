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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->date('date');
            $table->integer('absences_count')->default(0);
            $table->integer('tardies_count')->default(0);
            $table->string('brief_status')->nullable();
            $table->text('technical_topics')->nullable();
            $table->text('workshops_done')->nullable();
            $table->string('class_mood')->nullable();
            $table->boolean('objectives_met')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
