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
        Schema::create('nvrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('camera_id')->nullable();
            $table->foreign('camera_id')->references('id')->on('cameras')->onDelete('set null')->onUpdate('cascade');

            $table->string('code')->unique()->nullable();
            $table->string('brand')->nullable();
            $table->string('type')->nullable();
            $table->string('name');
            $table->string('initial')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nvrs');
    }
};
