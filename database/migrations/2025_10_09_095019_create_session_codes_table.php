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
        Schema::create('session_codes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('qr_code_id')->nullable();

            $table->foreign('qr_code_id')->references('id')->on('qr_codes')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('venue_id')->nullable();

            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('field_id')->nullable();

            $table->foreign('field_id')->references('id')->on('fields')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('recording_id')->nullable();

            $table->foreign('recording_id')->references('id')->on('recordings')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('generate_by_user_id')->nullable();

            $table->foreign('generate_by_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            $table->string('session_token')->nullable();

            $table->string('type')->nullable()->index();

            $table->string('status')->nullable()->index();

            $table->string('generated_code')->nullable()->unique();

            $table->dateTime('expired_at')->nullable();
            
            $table->timestamps();

            $table->index(['user_id', 'session_token']);
            $table->index(['qr_code_id', 'field_id']);
            $table->index(['venue_id', 'recording_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_codes');
    }
};
