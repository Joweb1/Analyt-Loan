<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class TenantSession
{
    protected ?string $organizationId = null;

    /**
     * Resolve and set the current tenant.
     */
    public function setTenantFromUser(): void
    {
        $user = Auth::user();

        if ($user && $user->organization_id) {
            $this->organizationId = $user->organization_id;
        } else {
            $this->organizationId = null;
        }
    }

    /**
     * Explicitly set the tenant (useful for testing or background jobs).
     */
    public function setTenantId(?string $id): void
    {
        $this->organizationId = $id;
    }

    /**
     * Get the current tenant ID.
     */
    public function getTenantId(): ?string
    {
        return $this->organizationId;
    }

    /**
     * Check if a tenant is currently set.
     */
    public function hasTenant(): bool
    {
        return ! is_null($this->organizationId);
    }

    /**
     * Ensure a tenant is set, or throw an exception.
     * This is the "Hard" enforcement part.
     */
    public function ensureTenant(): string
    {
        if (! $this->hasTenant()) {
            throw new \RuntimeException('Tenant (Organization) context is missing. Access denied.');
        }

        return $this->organizationId;
    }
}
