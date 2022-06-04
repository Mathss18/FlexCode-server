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
        config()->set('mail.mailers.tenant.host', session('config')->servidorSmtp);
        config()->set('mail.mailers.tenant.port', session('config')->portaSmtp);
        config()->set('mail.mailers.tenant.username', session('config')->usuarioSmtp);
        config()->set('mail.mailers.tenant.password', session('config')->senhaSmtp);
        config()->set('mail.mailers.tenant.encryption', session('config')->encryptionSmtp);


        config()->set('mail.from.address', 'flexmol@flexmol.com.br');
        config()->set('mail.from.name', session('tenant')->nome);
    }

    public function isAdmDomain(){
        return request()->getHost() == config('tenant.adm_domain');
    }
}
