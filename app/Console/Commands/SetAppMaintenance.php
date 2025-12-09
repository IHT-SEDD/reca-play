<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetAppMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-maintenance {state=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set APP_UNDER_MAINTENANCE value in .env and run optimize';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (!str_contains($envContent, 'APP_UNDER_MAINTENANCE')) {
            $this->error('APP_UNDER_MAINTENANCE was not found in the .env file.');
            return Command::FAILURE;
        }

        // HEADER BLOCK
        $this->info("");
        $this->info("==============================");
        $this->info(" UPDATE APP_UNDER_MAINTENANCE ");
        $this->info("==============================");
        $this->info("");

        // Get current value
        preg_match('/APP_UNDER_MAINTENANCE=(.*)/', $envContent, $matches);
        $current = trim($matches[1]);

        // Display current state (value in bold)
        $this->info(
            "Current APP_UNDER_MAINTENANCE value: <options=bold>{$current}</>"
        );

        $this->info("");

        // Determine new value
        $newState = $current === 'true' ? 'false' : 'true';
        $actionText = $current === 'true'
            ? "disable maintenance mode"
            : "enable maintenance mode";

        // First confirmation
        if (! $this->confirm("Do you want to update APP_UNDER_MAINTENANCE to {$newState}?")) {
            $this->info("Operation cancelled.");
            return Command::SUCCESS;
        }

        // Second confirmation
        if (! $this->confirm(
            "Are you sure you want to set APP_UNDER_MAINTENANCE to {$newState}? " .
                "This action will {$actionText}."
        )) {
            $this->info("Operation cancelled.");
            return Command::SUCCESS;
        }

        // Update .env
        $newContent = preg_replace(
            '/^APP_UNDER_MAINTENANCE=.*/m',
            "APP_UNDER_MAINTENANCE={$newState}",
            $envContent
        );

        file_put_contents($envPath, $newContent);
        $this->info("APP_UNDER_MAINTENANCE has been successfully updated to: {$newState}");

        // Optimize app
        $this->call('optimize');
        $this->info("Laravel cache has been optimized.");

        return Command::SUCCESS;
    }
}
