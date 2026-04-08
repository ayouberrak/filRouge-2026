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
        Schema::table('activities', function (Blueprint $table) {
            $table->text('objectives')->nullable();
            $table->text('context')->nullable();
            $table->text('exploration_points')->nullable();
            $table->string('work_rule')->default('Individuel');
            $table->text('resources')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['objectives', 'context', 'exploration_points', 'work_rule', 'resources']);
        });
    }
};
