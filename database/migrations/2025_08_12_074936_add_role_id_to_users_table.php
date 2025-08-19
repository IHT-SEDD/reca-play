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
        Schema::table('users', function (Blueprint $table) {
            # Add role_id column to users table
            $table->unsignedBigInteger('role_id')->nullable()->after('id');
            # Add foreign key constraint and set to null on delete and cascade on update
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            # Drop foreign key constraint and role_id column
            $table->dropForeign(['role_id']);
            # Drop the role_id column
            $table->dropColumn('role_id');
        });
    }
};
