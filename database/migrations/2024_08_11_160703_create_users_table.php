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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('family');
            $table->string('nickname')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('phone_register_code')->nullable();
            $table->string('forgot_password')->nullable();
            $table->timestamp('phone_register_code_expired_at')->nullable();
            $table->timestamp('forgot_password_expired_at')->nullable();
            $table->string('password');
            $table->tinyInteger('status')->default(0);
            $table->integer('photo_id')->nullable();
            $table->string('grade')->default("D");
            $table->integer('level')->default(0);
            $table->integer('xp')->default(0);
            $table->string('address')->nullable();
            $table->string('local_id')->nullable();
            $table->string('city_id')->nullable();
            $table->string('description')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('role')->default("User");
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
