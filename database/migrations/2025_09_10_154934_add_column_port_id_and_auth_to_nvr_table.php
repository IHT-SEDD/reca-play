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
            $table->unsignedBigInteger('port_id')->nullable()->after('ip_address');
            $table->foreign('port_id')->references('id')->on('ports')->onDelete('set null')->onUpdate('cascade');

            $table->string('auth_type')->nullable()->after('port_id');
            $table->string('username')->nullable()->after('auth_type');
            $table->string('password')->nullable()->after('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nvrs', function (Blueprint $table) {
            $table->dropForeign(['port_id']);
            $table->dropColumn(['port_id', 'auth_type', 'username', 'password']);
        });
    }
};
