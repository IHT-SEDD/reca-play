<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $output = new ConsoleOutput();
        $database = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $keyName = "Tables_in_{$database}";

        $excludedTables = [
            'cache',
            'cache_locks',
            'failed_jobs',
            'job_batches',
            'jobs',
            'migrations',
            'model_has_permissions',
            'model_has_roles',
            'password_reset_tokens',
            'permissions',
            'role_has_permissions',
            'sessions',
            'users',
        ];

        $indexedCount = 0;
        $logData = [];

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$keyName;

            if (in_array($tableName, $excludedTables) || !Schema::hasTable($tableName)) {
                continue;
            }

            $columns = Schema::getColumnListing($tableName);
            if (empty($columns)) {
                continue;
            }

            $existingIndexes = $this->getExistingIndexes($tableName);

            Schema::table($tableName, function (Blueprint $table) use (
                $columns,
                $existingIndexes,
                $tableName,
                &$indexedCount,
                &$logData
            ) {
                foreach ($columns as $column) {
                    if (str_ends_with($column, '_id')) {
                        $indexName = "{$tableName}_{$column}_index";

                        if (!in_array($indexName, $existingIndexes)) {
                            try {
                                $table->index($column, $indexName);
                                $indexedCount++;
                                $logData[$tableName][] = $column;
                            } catch (Throwable $e) {
                                // skip error index duplikat / table lock
                            }
                        }
                    }
                }
            });
        }

        if ($indexedCount > 0) {
            $output->writeln("\n<info>✅ INDEX DITAMBAHKAN:</info>");
            foreach ($logData as $table => $cols) {
                $output->writeln("<comment>- {$table}</comment>: " . implode(', ', $cols));
            }
            $output->writeln("<info>Total index baru: {$indexedCount}</info>\n");
        } else {
            $output->writeln("\n<fg=yellow>ℹ️ Tidak ada kolom _id baru yang perlu di-index.</>\n");
        }

        Log::info('[Migration] Index ditambahkan pada kolom _id:', $logData);
    }

    public function down(): void
    {
        $output = new ConsoleOutput();
        $database = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $keyName = "Tables_in_{$database}";

        $removed = [];

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$keyName;

            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $columns = Schema::getColumnListing($tableName);
            $foreignIndexes = $this->getForeignKeyIndexes($tableName);

            Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName, $foreignIndexes, &$removed) {
                foreach ($columns as $column) {
                    if (str_ends_with($column, '_id')) {
                        $indexName = "{$tableName}_{$column}_index";

                        // Skip index yang digunakan oleh foreign key
                        if (in_array($indexName, $foreignIndexes)) {
                            continue;
                        }

                        try {
                            $table->dropIndex($indexName);
                            $removed[$tableName][] = $column;
                        } catch (Throwable $e) {
                            // abaikan jika index tidak ada
                        }
                    }
                }
            });
        }

        if (!empty($removed)) {
            $output->writeln("\n<fg=red>🧹 INDEX DIHAPUS:</>");
            foreach ($removed as $table => $cols) {
                $output->writeln("<comment>- {$table}</comment>: " . implode(', ', $cols));
            }
        } else {
            $output->writeln("\n<fg=yellow>ℹ️ Tidak ada index yang dihapus.</>\n");
        }

        Log::info('[Migration] Index _id dihapus:', $removed);
    }

    protected function getExistingIndexes(string $tableName): array
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$tableName}");
            return collect($indexes)->pluck('Key_name')->unique()->toArray();
        } catch (Throwable $e) {
            return [];
        }
    }

    protected function getForeignKeyIndexes(string $tableName): array
    {
        try {
            $rows = DB::select("
                SELECT CONSTRAINT_NAME AS index_name
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);

            return collect($rows)->pluck('index_name')->unique()->toArray();
        } catch (Throwable $e) {
            return [];
        }
    }
};
