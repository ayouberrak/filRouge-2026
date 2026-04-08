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
        Schema::table('briefs', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('title');
            $table->text('context')->nullable()->after('description');
            $table->text('pedagogical_modalities')->nullable()->after('modality');
            $table->text('evaluation_modalities')->nullable()->after('pedagogical_modalities');
            $table->json('deliverables')->nullable()->after('resources');
            $table->json('performance_criteria')->nullable()->after('deliverables');
            $table->json('target_competencies')->nullable()->after('performance_criteria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('briefs', function (Blueprint $table) {
            $table->dropColumn([
                'image_url',
                'context',
                'pedagogical_modalities',
                'evaluation_modalities',
                'deliverables',
                'performance_criteria',
                'target_competencies'
            ]);
        });
    }
};
