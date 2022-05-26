<?php

namespace App\Http\Middleware\Tenant;

use App\Models\Configuracao;
use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant\Tenant;
use App\Tenant\ManagerTenant;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // dd(response()->json($request->getHost(),500));
        // dd(config('tenant.adm_domain'));
        $manager = app(ManagerTenant::class);

        if ($manager->isAdmDomain()) {
            return $next($request);
        }

        $tenant = $this->getTenant($request->getHost());

        if (!$tenant) {
            return response()->json(['error' => '[Middleware] Cliente não encontrado'], 404);
        } else {
            if($tenant->situacao == false){
                return response()->json(['error' => '[Middleware] Cliente Inativo'], 401);
            }
            $manager->setConnection($tenant);
            $this->setSession('tenant', $tenant);

            $config = Configuracao::where('situacao', true)->first();
            $this->setSession('config', $config);
            $manager->setSmtp();
        }

        return $next($request);
    }

    public function getTenant($host)
    {
        return Tenant::where('sub_dominio', $host)->first();
    }

    public function setSession($key, $value)
    {
        session()->put($key, $value);
    }
}
