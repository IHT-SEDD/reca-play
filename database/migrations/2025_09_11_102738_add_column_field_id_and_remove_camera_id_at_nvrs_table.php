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
        Schema::table('nvrs', function (Blueprint $table) {
            if (Schema::hasColumn('nvrs', 'camera_id')) {
                $table->dropForeign(['camera_id']);
                $table->dropColumn('camera_id');
            }

            $table->unsignedBigInteger('field_id')->nullable()->after('id');
            $table->foreign('field_id')->references('id')->on('fields')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nvrs', function (Blueprint $table) {
            $table->dropForeign(['field_id']);
            $table->dropColumn('field_id');

            $table->unsignedBigInteger('camera_id')->nullable()->after('id');
            $table->foreign('camera_id')
                ->references('id')
                ->on('cameras')
                ->onDelete('cascade');
        });
    }
};
