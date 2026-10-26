<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the "acting" tenant for the current request.
 *
 * - A client-admin is always locked to their own tenant_id.
 * - A super-admin has no tenant of their own; they may optionally
 *   "act as" a tenant, stored in the session. When not acting as one,
 *   tenant-scoped queries are NOT restricted (see TenantScope).
 *
 * Tier 1 models opt into scoping via the HasTenant trait.
 */
class CurrentTenant
{
    public const SESSION_KEY = 'acting_tenant_id';

    /** The tenant id that should scope queries, or null for "all" (super-admin, unscoped). */
    public static function id(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            $acting = session(self::SESSION_KEY);
            return $acting ? (int) $acting : null; // null => see everything
        }

        return $user->tenant_id;
    }

    public static function model(): ?Tenant
    {
        $id = self::id();
        return $id ? Tenant::find($id) : null;
    }

    /** Super-admin: scope the whole session to one tenant (impersonation-lite). */
    public static function actAs(?int $tenantId): void
    {
        if ($tenantId) {
            session([self::SESSION_KEY => $tenantId]);
        } else {
            session()->forget(self::SESSION_KEY);
        }
    }

    public static function isActingAs(): bool
    {
        return (bool) session(self::SESSION_KEY);
    }
}
