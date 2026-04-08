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
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->integer('tardies_count')->default(0)->after('absences_count');
            $table->text('technical_topics')->nullable()->after('brief_status');
            $table->text('workshops_done')->nullable()->after('technical_topics');
            $table->integer('class_mood')->default(3)->after('workshops_done'); // 1 to 5
            $table->boolean('objectives_met')->default(true)->after('class_mood');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn(['tardies_count', 'technical_topics', 'workshops_done', 'class_mood', 'objectives_met']);
        });
    }
};
