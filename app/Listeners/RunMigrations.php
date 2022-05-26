<?php

namespace App\Listeners;

use App\Events\Tenant\TenantMigrate;
use App\Tenant\Database\DatabaseManager;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;

class RunMigrations
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(TenantMigrate $event)
    {
        $tenant = $event->getTenant();
        $resp = Artisan::call('tenants:migrate', [
            'id' => $tenant->id,
        ]);
        if ($resp !== 0) {
            throw new Exception('Error running migrations');
        }

        $resp2 = Artisan::call('db:seed');
        // $resp2 = Artisan::call('tenants:seed', [
        //     'id' => $tenant->id,
        // ]);

        if ($resp2 !== 0) {
            throw new Exception('Error running seeders');
        }

        return true; // retorna true se tudo deu certo
    }
}
