<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Config;

class TenantService
{
    protected ?Company $currentTenant = null;

    public function setTenant(Company $company)
    {
        $this->currentTenant = $company;
    }

    public function getTenant(): ?Company
    {
        return $this->currentTenant;
    }

    public function tenantId(): ?int
    {
        return $this->currentTenant?->id;
    }
}