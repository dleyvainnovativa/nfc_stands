<?php

namespace App\Http\Controllers;

use App\Models\Stand;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * A client claims a physical tag printed with a code: they enter the code,
 * and if it's an unassigned blank tag it's attached to their tenant and set
 * active. Claiming happens from inside the authenticated panel (not the public
 * page), so blank tags can't be hijacked by whoever scans one first.
 */
class StandClaimController extends Controller
{
    public function show()
    {
        return view('stands.claim');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:16'],
        ]);

        $tenantId = CurrentTenant::id();
        if ($tenantId === null) {
            return back()->with('flash.warning',
                'Entra a un cliente antes de reclamar una etiqueta.');
        }

        // Look up across all tenants (blank tags have tenant_id = null).
        $stand = Stand::withoutTenantScope()->where('code', $data['code'])->first();

        if (! $stand) {
            return back()->withInput()->with('flash.error', 'No se encontró ninguna etiqueta con ese código.');
        }

        if (! $stand->isClaimable()) {
            return back()->withInput()->with('flash.error',
                'Esa etiqueta ya está asignada.');
        }

        $stand->tenant_id = $tenantId;
        $stand->status = Stand::STATUS_ACTIVE;
        $stand->claimed_at = Carbon::now();
        // Start empty; the admin picks the destination on the next screen.
        if (empty($stand->destination_config)) {
            $stand->destination_type = Stand::TYPE_DIRECT;
            $stand->destination_config = ['url' => ''];
        }
        $stand->save();

        return redirect()->route('stands.edit', $stand)
            ->with('flash.success', 'Etiqueta reclamada: define su destino.');
    }
}
