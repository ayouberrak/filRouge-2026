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
        Schema::create('briefs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('objectives')->nullable();
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->enum('difficulty', ['EASY', 'MEDIUM', 'HARD'])->default('EASY');
            $table->enum('modality', ['INDIVIDUAL', 'GROUP'])->default('INDIVIDUAL');
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'IN_PROGRESS', 'COMPLETED', 'ARCHIVED'])->default('DRAFT');
            $table->json('tags')->nullable();
            $table->json('resources')->nullable();
            $table->string('file')->nullable();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('briefs');
    }
};
