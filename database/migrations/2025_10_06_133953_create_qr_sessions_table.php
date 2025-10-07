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
        Schema::create('qr_sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('qr_code_id')->nullable();
            $table->foreign('qr_code_id')->references('id')->on('qr_codes')->onDelete('set null')->onUpdate('cascade');

            $table->string('qr_code')->nullable();
            $table->string('type')->nullable();
            $table->json('qr_data')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->unique('user_id', 'user_qr_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_sessions');
    }
};
