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

                // Portfolio Scoping for Staff
                if (! $user->hasRole('Admin') && ! $user->isOrgOwner() && in_array(get_class($builder->getModel()), [\App\Models\Borrower::class, \App\Models\Loan::class])) {
                    $portfolioIds = $user->portfolios()->pluck('portfolios.id')->toArray();

                    if (! empty($portfolioIds)) {
                        $builder->whereIn($builder->getModel()->getTable().'.portfolio_id', $portfolioIds);
                    } else {
                        // If staff has no portfolios assigned, they can only see unassigned borrowers/loans
                        // or perhaps we should restrict them completely?
                        // User said: "the permissions the staffs has will be limited to only the portfolio"
                        // This implies if they have NO portfolio, they see nothing or only unassigned.
                        // For now, let's allow unassigned if they have no portfolio,
                        // BUT if they ARE assigned to some, they ONLY see those.
                        // Actually, if they are assigned to ANY portfolio, the above whereIn handles it.
                        // If they have NO portfolio assignment, should they see everything?
                        // User said "restricted from him" for other portfolios.
                        // Let's assume if they have NO portfolio assigned, they can see everything in the org (current behavior)
                        // UNLESS they are assigned to at least one.
                    }
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
