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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('activity_type'); // veille, workshop, etc.
            
            $table->dateTime('scheduled_at')->nullable();
            $table->string('duration')->nullable(); // For display e.g. "30 min"
            $table->integer('duration_minutes')->default(0); 

            $table->integer('points')->default(0);
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();

            $table->text('objectives')->nullable();
            $table->text('context')->nullable();
            $table->text('exploration_points')->nullable();
            $table->string('work_rule')->default('Solo');
            $table->text('resources')->nullable();
            $table->boolean('is_points_distributed')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
