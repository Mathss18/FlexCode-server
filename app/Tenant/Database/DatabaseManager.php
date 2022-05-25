<?php

namespace App\Tenant\Database;

use App\Models\Tenant\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Collective\Remote\RemoteFacade as SSH;

class DatabaseManager
{

    public function createDatabase(Tenant $tenant)
    {
        $resp = DB::statement(
                "CREATE DATABASE IF NOT EXISTS `{$tenant->db_database}`
            DEFAULT CHARACTER SET utf8mb4
            DEFAULT COLLATE utf8mb4_unicode_ci"
            );

        return $resp;
    }

    public function createRemoteDatabase(Tenant $tenant)
    {
        $query = "CREATE DATABASE IF NOT EXISTS `{$tenant->db_database}`
        DEFAULT CHARACTER SET utf8mb4
        DEFAULT COLLATE utf8mb4_unicode_ci;";

        $process = SSH::into('production')->run([
            'cd api.allmacoding.com/',
            'git pull',

        ]);
        // dd($process);
        // echo '<pre>';
        // print_r($process);
        // echo '</pre>';
        // die;
    }

    public function isMainDomain()
    {
        return request()->getHost() == config('tenant.main_domain');
    }
}
