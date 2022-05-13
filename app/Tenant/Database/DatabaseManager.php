<?php

namespace App\Tenant\Database;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    public function isMainDomain()
    {
        return request()->getHost() == config('tenant.main_domain');
    }
}
