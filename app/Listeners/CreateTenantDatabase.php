<?php

namespace App\Listeners;

use App\Events\Tenant\TenantCreate;
use App\Events\Tenant\TenantMigrate;
use App\Tenant\Database\DatabaseManager;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateTenantDatabase
{
    private $databaseManager;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(TenantCreate $event)
    {
        $tenant = $event->getTenant();

        if (!$this->databaseManager->createDatabase($tenant)) {
            throw new Exception('Error creating database');
        }

        //run migrations
        event(new TenantMigrate($tenant));



    }
}
