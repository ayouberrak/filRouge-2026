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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'speciality',
                'avatar_url',
                'location',
                'bio',
                'skills'
            ]);
        });

        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'start_at',
                'end_at'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('speciality')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('location')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
        });

        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
        });
    }
};
