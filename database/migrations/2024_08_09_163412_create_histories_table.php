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
            $table->string("title")->nullable();
            $table->integer("price");
            $table->integer("capacity");
            $table->unsignedBigInteger("game_id");
            $table->unsignedBigInteger("user_id");
            $table->integer("character_id")->nullable();
            $table->string("game_scenario")->nullable();
            $table->tinyInteger("status");
            $table->tinyInteger("score")->nullable();
            $table->string("season")->nullable();
            $table->integer("xp")->nullable();

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
