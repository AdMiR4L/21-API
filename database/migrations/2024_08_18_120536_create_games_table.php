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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string("title")->nullable();
            $table->string("description")->nullable();
            $table->string("meta_description")->nullable();
            $table->string("meta_keywords")->nullable();
            $table->integer("price");
            $table->string("capacity");
            $table->string("extra_capacity");
            $table->string("available_capacity");
            $table->integer("game_scenario")->nullable();
            $table->text("game_characters")->nullable();
            $table->string("salon");
            $table->string("grade");
            $table->string("clock");
            $table->tinyInteger("paying")->default(1);
            $table->tinyInteger("status")->default(0);
            $table->tinyInteger("special")->default(0);
            $table->unsignedInteger("god_id")->nullable();
            $table->unsignedInteger("photo_id")->nullable();
            $table->tinyInteger("win_side")->nullable();
            $table->unsignedInteger("mvp")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
