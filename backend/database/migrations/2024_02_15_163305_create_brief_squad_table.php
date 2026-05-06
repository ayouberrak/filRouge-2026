<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('brief_squad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brief_id')->constrained()->onDelete('cascade');
            $table->foreignId('squad_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('brief_squad');
    }
};
