<?php

namespace App\Concerns;

use App\Models\Tenant;
use App\Scopes\TenantScope;
use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to any tenant-owned model (stands, surveys, scan_events, ... in Tier 1+).
 *
 *   use App\Concerns\HasTenant;
 *   class Stand extends Model { use HasTenant; }
 *
 * - Adds the global TenantScope so reads are automatically filtered.
 * - Auto-fills tenant_id on create from the acting tenant.
 * - Exposes a tenant() relation and a withoutTenantScope() helper.
 */
trait HasTenant
{
    protected static function bootHasTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = CurrentTenant::id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Escape hatch for super-admin / console contexts that must cross tenants. */
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
