<?php

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Tenant\ManagerTenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantSeed extends Command
{
    private $managerTenant;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tenants seeds';

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
                $this->runMigration($tenant);
            }catch (\Exception $e){
                $this->error($e->getMessage());
            }
        }
        else{
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                $this->runMigration($tenant);
            }
        }


    }

    public function runMigration(Tenant $tenant)
    {
        $this->managerTenant->setConnection($tenant);

        $command = 'db:seed';
        $this->info("Running seeds for tenant {$tenant->nome}");

        $resp = Artisan::call($command);

        if ($resp === 0) {
            $this->info("Seeds for tenant {$tenant->nome} executed successfully");
        } else {
            $this->error("Seeds for tenant {$tenant->nome} failed");
        }
        $this->info("\n ------------------------------------------------- \n");
    }
}
