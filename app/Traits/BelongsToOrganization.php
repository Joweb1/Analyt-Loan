<?php

namespace App\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrganization
{
    /**
     * Boot the trait to add global scope and creating event.
     */
    protected static function bootBelongsToOrganization(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && ! $model->organization_id) {
                $model->organization_id = Auth::user()->organization_id;
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // Exempt App Owner from global scope to allow platform-wide visibility
                if ($user->isAppOwner()) {
                    return;
                }

                if ($orgId = $user->organization_id) {
                    $builder->where($builder->getModel()->getTable().'.organization_id', $orgId);
                }
            }
        });
    }

    /**
     * Relationship to the Organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
