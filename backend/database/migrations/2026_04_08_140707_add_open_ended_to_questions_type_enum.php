<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modifier l'ENUM de la colonne 'type' pour inclure 'open_ended' et 'code'
        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('multiple_choice', 'code_simulation', 'open_ended', 'code') NOT NULL DEFAULT 'multiple_choice'");
    }

    public function down(): void
    {
        // Revenir à la définition originale (les valeurs 'open_ended'/'code' seront perdues)
        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('multiple_choice', 'code_simulation') NOT NULL DEFAULT 'multiple_choice'");
    }
};
