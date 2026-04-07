<?php

namespace App\Traits;

use App\Models\AuditTrail;
use App\Services\TenantSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newValues = $model->getChanges();

            // Ignore timestamp-only updates
            unset($oldValues['updated_at'], $newValues['updated_at']);

            if (! empty($newValues)) {
                $model->logAudit('updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getAttributes(), null);
        });
    }

    protected function logAudit(string $event, ?array $old, ?array $new): void
    {
        // Don't log AuditTrail changes themselves
        if (get_class($this) === AuditTrail::class) {
            return;
        }

        AuditTrail::create([
            'organization_id' => $this->organization_id ?? app(TenantSession::class)->getTenantId(),
            'user_id' => Auth::id(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => $event,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function auditTrails(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuditTrail::class, 'auditable');
    }
}
