<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
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
        // return response()->json($request->getHost(),500);
        $manager = app(ManagerTenant::class);

        if($manager->isMainDomain()){
            return $next($request);
        }

        $tenant = $this->getTenant($request->getHost());

        if(!$tenant) {
            return response()->json(['error' => '[Middleware] Tenant not found'], 404);
        }
        else{
            $manager->setConnection($tenant);
            $this->setSession($tenant);
        }

        return $next($request);
    }

    public function getTenant($host)
    {
        return Tenant::where('sub_dominio', $host)->first();
    }

    public function setSession($tenant){
        session()->put('tenant', $tenant);
    }
}
