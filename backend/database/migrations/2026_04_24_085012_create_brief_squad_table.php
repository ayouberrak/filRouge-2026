<?php
 
 use Illuminate\Database\Migrations\Migration;
 use Illuminate\Database\Schema\Blueprint;
 use Illuminate\Support\Facades\Schema;
 
 return new class extends Migration
 {
     public function up(): void
     {
         Schema::create('brief_squad', function (Blueprint $table) {
             $table->id();
             $table->foreignId('brief_id')->constrained('briefs')->cascadeOnDelete();
             $table->foreignId('squad_id')->constrained('squads')->cascadeOnDelete();
             $table->timestamps();
         });
     }
 
     public function down(): void
     {
         Schema::dropIfExists('brief_squad');
     }
 };
