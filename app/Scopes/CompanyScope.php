<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Services\TenantService;

class CompanyScope implements Scope
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        // Inject the TenantService here.
        $this->tenantService = $tenantService;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply the scope if a tenant has been successfully identified by the middleware.
        if ($this->tenantService->getTenant()) {
            // Apply a simple WHERE clause to ensure data isolation.
            $builder->where('company_id', $this->tenantService->tenantId());
        }
    }
}