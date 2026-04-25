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
        Schema::create('livrables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brief_id')->constrained('briefs')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('squad_id')->nullable()->constrained('squads')->cascadeOnDelete();
            $table->string('link');
            $table->enum('status', ['SUBMITTED', 'VALIDATED', 'REJECTED'])->default('SUBMITTED');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livrables');
    }
};
