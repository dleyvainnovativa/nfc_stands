<?php

namespace App\Scopes;

use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that constrains tenant-owned models to the acting tenant.
 *
 * When CurrentTenant::id() is null (a super-admin not acting as any tenant),
 * no constraint is applied and the query sees every tenant's rows.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = CurrentTenant::id();

        if ($tenantId !== null) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }
}
