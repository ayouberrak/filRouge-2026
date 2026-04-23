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
        Schema::table('activities', function (Blueprint $row) {
            $row->dropColumn([
                'objectives',
                'context',
                'exploration_points',
                'work_rule',
                'resources'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $row) {
            $row->text('objectives')->nullable();
            $row->text('context')->nullable();
            $row->text('exploration_points')->nullable();
            $row->text('work_rule')->nullable();
            $row->text('resources')->nullable();
        });
    }
};
