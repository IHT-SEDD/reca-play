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
        Schema::table('recordings', function (Blueprint $table) {
            $table->unsignedBigInteger('session_code_id')->nullable()->after('camera_id');
            $table->foreign('session_code_id')->references('id')->on('session_codes')->onDelete('set null')->onUpdate('cascade');
            $table->string('session_token')->nullable()->after('session_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->dropForeign(['session_code_id']);
            $table->dropColumn('session_code_id');
            $table->dropColumn('session_token');
        });
    }
};
