<?php

namespace App\Tenant;

use App\Models\Tenant\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManagerTenant{

    public function setConnection(Tenant $tenant){
        DB::purge('tenant');

        config()->set('database.connections.tenant.host', $tenant->db_host);
        config()->set('database.connections.tenant.port', $tenant->db_port);
        config()->set('database.connections.tenant.database', $tenant->db_database);
        config()->set('database.connections.tenant.username', $tenant->db_username);
        config()->set('database.connections.tenant.password', $tenant->db_password);

        DB::reconnect('tenant');

        Schema::connection('tenant')->getConnection()->reconnect();
    }

    public function setSmtp(){
        config()->set('mail.mailers.smtp.host', session('config')->servidorSmtp ?? null);
        config()->set('mail.mailers.smtp.port', session('config')->portaSmtp ?? null);
        config()->set('mail.mailers.smtp.username', session('config')->usuarioSmtp ?? null);
        config()->set('mail.mailers.smtp.password', session('config')->senhaSmtp ?? null);

        config()->set('mail.from.address', session('config')->email ?? null);
        config()->set('mail.from.name', session('tenant')->nome ?? null);
    }

    public function isAdmDomain(){
        return request()->getHost() == config('tenant.adm_domain');
    }
}
