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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("game_id");
            $table->unsignedBigInteger("user_id");
            $table->integer("character_id")->nullable();
            $table->string("game_scenario")->nullable();
            $table->tinyInteger("score")->nullable();
            $table->tinyInteger("win")->nullable();
            $table->integer("xp")->nullable();
            $table->string("achievements")->nullable();
            $table->string("chair_number")->nullable();
            //$table->unsignedBigInteger("order_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
