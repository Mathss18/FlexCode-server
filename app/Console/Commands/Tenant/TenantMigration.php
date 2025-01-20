<?php

namespace App\Console\Commands\Tenant;

use App\Models\Tenant\Tenant;
use App\Tenant\ManagerTenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantMigration extends Command
{
    /**
     * Include the rollback option in the command signature.
     *
     * Example usage:
     *   php artisan tenants:migrate --rollback
     *
     * You can still pass in an {id} if you want to target a specific tenant.
     */
    protected $signature = 'tenants:migrate {id?} {--fresh : Drop all tables and re-run all migrations} {--rollback : Rollback the last database migration}';

    protected $description = 'Run tenants migrations or rollbacks';

    /**
     * @var ManagerTenant
     */
    private $managerTenant;

    public function __construct(ManagerTenant $managerTenant)
    {
        parent::__construct();
        $this->managerTenant = $managerTenant;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // If an id is passed, we only migrate/rollback for that single tenant.
        if ($this->argument('id')) {
            try {
                $tenant = Tenant::findOrFail($this->argument('id'));
                $this->runMigration($tenant);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else {
            // Otherwise, we run the migration/rollback for all tenants.
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                $this->runMigration($tenant);
            }
        }
    }

    /**
     * Determine which Artisan command to run based on given options (fresh, rollback).
     * Then run the command for the given tenant.
     */
    public function runMigration(Tenant $tenant)
    {
        // Switch connection to this specific tenant.
        $this->managerTenant->setConnection($tenant);

        // Decide which migration command to run.
        // Priority: --fresh first, then --rollback, otherwise run normal migrate.
        $command = 'migrate';
        if ($this->option('fresh')) {
            $command = 'migrate:fresh';
        } elseif ($this->option('rollback')) {
            $command = 'migrate:rollback';
        }

        $this->info("Running '{$command}' for tenant: {$tenant->nome}");

        // Run the Artisan command. We force it so it doesn't prompt in production.
        $resp = Artisan::call($command, [
            '--force' => true,
            '--path'  => '/database/migrations/tenant',
        ]);

        // Display results based on the return code.
        if ($resp === 0) {
            $this->info("Command '{$command}' for tenant {$tenant->nome} executed successfully.");
        } else {
            $this->error("Command '{$command}' for tenant {$tenant->nome} failed.");
        }

        $this->info("\n-------------------------------------------------\n");
    }
}
