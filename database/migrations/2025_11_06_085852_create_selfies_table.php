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
        Schema::create('selfies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('field_id')->nullable();
            $table->foreign('field_id')->references('id')->on('fields')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('camera_id')->nullable();
            $table->foreign('camera_id')->references('id')->on('cameras')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('session_code_id')->nullable();
            $table->foreign('session_code_id')->references('id')->on('session_codes')->onDelete('set null')->onUpdate('cascade');

            $table->string('session_token')->nullable();
            $table->string('photo_name');
            $table->bigInteger('duration');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('status')->default('capturing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selfies');
    }
};
