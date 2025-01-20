<?php

namespace App\Console\Commands\Tenant;

use App\Models\Tenant\Tenant;
use App\Tenant\ManagerTenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantMigrationRollback extends Command
{
    private $managerTenant;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate:rollback {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tenants migrations rollback';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ManagerTenant $managerTenant)
    {
        parent::__construct();
        $this->managerTenant = $managerTenant;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        if ($this->argument('id')) {
            try{
                $tenant = Tenant::findOrFail($this->argument('id'));
                $this->managerTenant->setConnection($tenant);
                $this->runMigrationRollback($tenant);
            }catch (\Exception $e){
                $this->error($e->getMessage());
            }
        }
        else{
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                $this->runMigrationRollback($tenant);
            }
        }


    }

    public function runMigrationRollback(Tenant $tenant)
    {
        $this->managerTenant->setConnection($tenant);

        $command =  'migrate:rollback';

        $this->info("Running migrations rollback for tenant {$tenant->nome}");

        $resp = Artisan::call($command, [
            '--force' => true,
            '--path' => '/database/migrations/tenant',
        ]);

        if ($resp === 0) {
            $this->info("Migrations for tenant {$tenant->nome} executed successfully");
        } else {
            $this->error("Migrations for tenant {$tenant->nome} failed");
        }
        $this->info("\n ------------------------------------------------- \n");
    }
}
