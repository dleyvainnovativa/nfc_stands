<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GooglePlace;
use App\Models\Stand;
use App\Models\StandDailyStat;
use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Super-admin only: monitor every client (tenant) from one screen and
 * onboard new ones. Counts cross tenant boundaries, so each aggregate
 * query drops the TenantScope explicitly.
 */
class TenantsController extends Controller
{
    public function index()
    {
        $since = Carbon::today()->subDays(29)->toDateString();
        $drop = fn ($q) => $q->withoutGlobalScope(TenantScope::class);

        $tenants = Tenant::query()
            ->withCount([
                'stands' => $drop,
                'stands as active_stands_count' => fn ($q) => $q->withoutGlobalScope(TenantScope::class)
                    ->where('status', Stand::STATUS_ACTIVE),
                'surveys' => $drop,
                'googlePlaces' => $drop,
                'surveyResponses' => $drop,
                'users' => fn ($q) => $q,
            ])
            ->orderBy('name')
            ->get();

        // Scans in the last 30 days, summed per tenant.
        $scans30 = StandDailyStat::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('stat_date', '>=', $since)
            ->selectRaw('tenant_id, SUM(scan_count) as total')
            ->groupBy('tenant_id')
            ->pluck('total', 'tenant_id');

        // Average Google rating per tenant.
        $ratings = GooglePlace::query()
            ->withoutGlobalScope(TenantScope::class)
            ->whereNotNull('rating')
            ->selectRaw('tenant_id, AVG(rating) as avg_rating')
            ->groupBy('tenant_id')
            ->pluck('avg_rating', 'tenant_id');

        return view('admin.tenants.index', compact('tenants', 'scans30', 'ratings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:160'],
            'contact_email'  => ['nullable', 'email', 'max:190'],
            'contact_phone'  => ['nullable', 'string', 'max:40'],
            'admin_name'     => ['required', 'string', 'max:160'],
            'admin_email'    => ['required', 'email', 'max:190', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        $tenant = Tenant::create([
            'name'          => $data['name'],
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'is_active'     => true,
        ]);

        User::create([
            'name'      => $data['admin_name'],
            'email'     => $data['admin_email'],
            'password'  => Hash::make($data['admin_password']),
            'role'      => User::ROLE_CLIENT_ADMIN,
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        return redirect()->route('admin.tenants.index')
            ->with('flash.success', 'Cliente «' . $tenant->name . '» creado con su administrador.');
    }
}
