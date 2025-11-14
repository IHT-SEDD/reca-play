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
            $table->dropForeign(['camera_id']);
            $table->dropColumn('camera_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->unsignedBigInteger('camera_id')->nullable();
            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }
};
