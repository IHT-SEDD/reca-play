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
        Schema::create('captured_selfies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('selfie_id')->nullable();
            $table->foreign('selfie_id')->references('id')->on('selfies')->onDelete('set null')->onUpdate('cascade');

            $table->string('pict_path')->nullable();
            $table->string('pict_filename')->nullable();
            $table->bigInteger('pict_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captured_selfies');
    }
};
