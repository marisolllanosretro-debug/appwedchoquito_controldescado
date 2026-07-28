<?php

namespace App\Console\Commands;

use Database\Seeders\RolesSeeder;
use Illuminate\Console\Command;

class IsinutaSyncRoles extends Command
{
    protected $signature = 'isinuta:sync-roles';

    protected $description = 'Sync legacy role data';

    public function handle(): void
    {
        $this->call(RolesSeeder::class);

        $this->info('Roles seeded successfully.');
    }
}
