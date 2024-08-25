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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->float('amount');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('game_id');
            $table->tinyInteger('status')->default(0);
            $table->string('method');
            $table->string('resCode')->nullable();
            $table->string('type')->nullable();
            $table->string('token')->nullable();
            $table->string('zarin_status')->nullable();
            $table->string('RefID')->nullable();
            $table->string('authority')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
