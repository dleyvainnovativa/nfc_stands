<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\RedirectResponse;

/**
 * Super-admin only: scope the session to one tenant ("view as"), or clear it.
 * Tier 1 uses this so a super-admin can inspect a client's stands/stats
 * through the same screens the client sees.
 */
class TenantSwitchController extends Controller
{
    public function enter(Tenant $tenant): RedirectResponse
    {
        CurrentTenant::actAs($tenant->id);

        return redirect()->route('dashboard')
            ->with('flash.info', 'Viewing as ' . $tenant->name . '.');
    }

    public function exit(): RedirectResponse
    {
        CurrentTenant::actAs(null);

        return redirect()->route('dashboard')
            ->with('flash.info', 'Back to platform view.');
    }
}
