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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('slug')->unique();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('video_link')->nullable();
            $table->string('source_name')->nullable();
            $table->tinyInteger('status')->unsigned();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('photo_id');
            $table->unsignedBigInteger('category_id');

            $table->integer('like')->default(0);
            $table->integer('dislike')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
