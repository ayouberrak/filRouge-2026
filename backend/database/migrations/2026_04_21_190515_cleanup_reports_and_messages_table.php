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
            $table->dropColumn('tardies_count');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_url', 'read_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->integer('tardies_count')->default(0);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->string('attachment_url')->nullable();
            $table->json('read_by')->nullable();
        });
    }
};
