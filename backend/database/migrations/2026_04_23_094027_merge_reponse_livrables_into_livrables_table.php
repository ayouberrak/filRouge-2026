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
        // Add formateur_id and formateur_message to livrables
        Schema::table('livrables', function (Blueprint $table) {
            $table->foreignId('formateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('formateur_message')->nullable();
        });

        // Drop reponse_livrables
        Schema::dropIfExists('reponse_livrables');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration logic because we're permanently cleaning up
    }
};
