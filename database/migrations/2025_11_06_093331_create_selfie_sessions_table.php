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
        Schema::create('selfie_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            $table->string('session_token')->nullable();

            $table->unsignedBigInteger('selfie_id')->nullable();
            $table->foreign('selfie_id')->references('id')->on('selfies')->onDelete('set null')->onUpdate('cascade');

            $table->string('qr_code')->nullable();
            $table->string('status')->default('prepare');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->unique('user_id', 'user_qr_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selfie_sessions');
    }
};
