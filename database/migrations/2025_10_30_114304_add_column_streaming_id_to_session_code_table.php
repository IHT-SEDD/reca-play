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
        Schema::table('session_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('streaming_id')->nullable()->after('recording_id');
            $table->foreign('streaming_id')->references('id')->on('streamings')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_codes', function (Blueprint $table) {
            $table->dropForeign(['streaming_id']);
            $table->dropColumn('streaming_id');
        });
    }
};
