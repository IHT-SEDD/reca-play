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
        Schema::create('recording_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recording_id')->nullable();
            $table->foreign('recording_id')->references('id')->on('recordings')->onDelete('set null')->onUpdate('cascade');

            $table->string('qr_code')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_logs');
    }
};
