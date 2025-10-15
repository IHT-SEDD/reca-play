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
        Schema::table('qr_sessions', function (Blueprint $table) {
            $table->string('qr_token')->nullable()->after('session_token');
            $table->dropColumn('qr_data');
            $table->dropColumn('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_sessions', function (Blueprint $table) {
            $table->dropColumn('qr_token');
            $table->json('qr_data')->nullable();
            $table->string('qr_code')->nullable();
        });
    }
};
