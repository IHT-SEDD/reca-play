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
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            $table->unsignedBigInteger('qr_code_id')->nullable();
            $table->foreign('qr_code_id')->references('id')->on('qr_codes')->onDelete('set null')->onUpdate('cascade');

            $table->unsignedBigInteger('session_code_id')->nullable();
            $table->foreign('session_code_id')->references('id')->on('session_codes')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('recording_id')->nullable();
            $table->foreign('recording_id')->references('id')->on('recordings')->onDelete('set null')->onUpdate('cascade');

            $table->string('type')->nullable()->index();
            $table->string('session_token')->nullable()->index();

            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->dateTime('active_at')->nullable();
            $table->dateTime('inactive_at')->nullable();

            $table->string('status')->nullable()->index();
            $table->string('action')->nullable();
            $table->timestamps();

            $table->index(['session_code_id', 'session_token']);
            $table->index(['user_id', 'recording_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};
