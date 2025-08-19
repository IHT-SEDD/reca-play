<?php

namespace App\Console\Commands;

use Database\Seeders\SuperAdminSeeder;
use Illuminate\Console\Command;

class RollbackSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rollback-super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the super admin user and remove their role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Are you sure want to delete Super Admin?')) {
            (new SuperAdminSeeder)->rollback();
            $this->info('✅ Super Admin deleted successfully.');
        } else {
            $this->info('❌ Action cancelled.');
        }
    }
}
