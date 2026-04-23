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
            $table->string('image_url')->nullable();
            $table->text('description');
            $table->text('context')->nullable();
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->string('modality')->default('INDIVIDUAL');
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'IN_PROGRESS', 'COMPLETED'])->default('DRAFT');
            $table->json('tags')->nullable();
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
