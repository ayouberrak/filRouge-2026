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
        // 1. Supprimer les tables du Marketplace
        Schema::dropIfExists('marketplace_orders');
        Schema::dropIfExists('products');

        // 2. Supprimer les colonnes liées aux points dans les autres tables
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('total_points');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['points', 'is_points_distributed']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne définit pas le rollback ici car l'utilisateur veut une suppression totale
    }
};
