<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tables to process
     */
    private array $tables = [
        'qr_sessions',
        'record_sessions',
        'recordings',
        'session_logs',
        'session_codes',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'session_token')) {
                continue;
            }

            if ($this->hasUniqueIndex($table, 'session_token')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->unique('session_token', "{$table}_session_token_unique");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {

            if (!Schema::hasColumn($table, 'session_token')) {
                continue;
            }

            if (!$this->hasUniqueIndex($table, 'session_token')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropUnique("{$table}_session_token_unique");
            });
        }
    }

    /**
     * Check if a column already has a unique index
     */
    private function hasUniqueIndex(string $table, string $column): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `$table`");

        foreach ($indexes as $index) {
            if (
                $index->Column_name === $column &&
                $index->Non_unique == 0
            ) {
                return true;
            }
        }

        return false;
    }
};
