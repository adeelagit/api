<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Company;
use App\Services\TenantService;
use Illuminate\Support\Facades\Route;

class IdentifyTenant
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        // Laravel's Service Container injects the TenantService singleton here.
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/register')) {
            return $next($request);
        }

        $host = $request->getHost();
        $hostParts = explode('.', $host);
        $subdomain = $hostParts[0];

        $ignoredSubdomains = ['www', 'api', env('APP_DOMAIN_MAIN', 'localhost')];
        $isIgnoredHost = in_array($subdomain, $ignoredSubdomains) || str_contains($host, '127.0.0.1');

        if ($isIgnoredHost) {
            // Check for the custom header if using a non-subdomain environment (like 127.0.0.1)
            $headerSubdomain = $request->header('X-Tenant-Subdomain');
            
            if ($headerSubdomain) {
                // If the header is present, use it for tenant lookup
                $subdomain = $headerSubdomain;
            } else {
                // If no subdomain is available (via host OR header), continue without setting tenant.
                // This allows un-tenanted routes (like the main page) to work,
                // but the AuthController@login must check for this.
                return $next($request);
            }
        }

        // if (in_array($subdomain, $ignoredSubdomains)) {
        //      return $next($request);
        // }

        $company = Company::where('subdomain', $subdomain)->first();

        if (!$company) {
            // Stop the request if a specific subdomain is used but no tenant is found.
            return response()->json(['message' => 'Tenant not found.'], 404);
        }

        // 4. Set the tenant context for the rest of the request lifecycle.
        $this->tenantService->setTenant($company);

        return $next($request);
    }
}