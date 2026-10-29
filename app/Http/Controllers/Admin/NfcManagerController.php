<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stand;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Super-admin NFC writer console (Web NFC, same-origin — protected by the
 * existing session + role:super_admin guard, so no token layer needed).
 *
 * The page reads/writes physical tags in the browser (Chrome Android); these
 * endpoints are the server side of the sync: list the pending queue, resolve a
 * scanned code back to its stand, and record when a tag has been encoded.
 *
 * All queries run WITHOUT the tenant scope on purpose — the console works
 * across every client and the tenant-less "blank pool" at once.
 */
class NfcManagerController extends Controller
{
    public function index()
    {
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        $blankCount = Stand::withoutTenantScope()->whereNull('tenant_id')->count();

        return view('admin.nfc', compact('tenants', 'blankCount'));
    }

    /** JSON: stands for a tenant (id), the blank pool ('pool'), or all. */
    public function stands(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant' => ['nullable', 'string', 'max:20'], // numeric id | 'pool' | ''(all)
            'encoded' => ['nullable', 'in:0,1'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $query = Stand::withoutTenantScope()->with('tenant:id,name');

        if (($data['tenant'] ?? null) === 'pool') {
            $query->whereNull('tenant_id');
        } elseif (! empty($data['tenant']) && ctype_digit($data['tenant'])) {
            $query->where('tenant_id', (int) $data['tenant']);
        }

        if (isset($data['encoded'])) {
            $data['encoded'] === '1'
                ? $query->whereNotNull('encoded_at')
                : $query->whereNull('encoded_at');
        }

        if (! empty($data['q'])) {
            $term = '%' . $data['q'] . '%';
            $query->where(fn ($w) => $w->where('label', 'like', $term)->orWhere('code', 'like', $term));
        }

        $stands = $query
            ->orderByRaw('encoded_at is null desc') // pending first
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        return response()->json([
            'stands' => $stands->map(fn (Stand $s) => $this->present($s))->values(),
            'pending' => $stands->whereNull('encoded_at')->count(),
            'total' => $stands->count(),
        ]);
    }

    /** JSON: resolve a scanned code (cross-tenant) so the operator sees what the tag is. */
    public function lookup(string $code): JsonResponse
    {
        $stand = Stand::withoutTenantScope()->with('tenant:id,name')->where('code', $code)->first();

        return $stand
            ? response()->json(['found' => true, 'stand' => $this->present($stand)])
            : response()->json(['found' => false]);
    }

    /** Record that a physical tag has been written for this stand. */
    public function markEncoded(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['kind' => ['nullable', 'in:url,wifi']]);
        $stand = Stand::withoutTenantScope()->findOrFail($id);

        $stand->forceFill([
            'encoded_at' => Carbon::now(),
            'encoded_by' => $request->user()->id,
            'encoded_kind' => $data['kind']
                ?? ($stand->destination_type === Stand::TYPE_WIFI ? 'wifi' : 'url'),
        ])->save();

        return response()->json(['ok' => true, 'stand' => $this->present($stand->fresh('tenant'))]);
    }

    /** Clear the encoded flag — inventory correction (tag wiped / reissued). */
    public function unmarkEncoded(int $id): JsonResponse
    {
        $stand = Stand::withoutTenantScope()->findOrFail($id);
        $stand->forceFill(['encoded_at' => null, 'encoded_by' => null, 'encoded_kind' => null])->save();

        return response()->json(['ok' => true, 'stand' => $this->present($stand->fresh('tenant'))]);
    }

    /** Shape a stand for the console. Payload carries exactly what to write. */
    private function present(Stand $s): array
    {
        return [
            'id' => $s->id,
            'code' => $s->code,
            'label' => $s->label,
            'tenant_id' => $s->tenant_id,
            'tenant' => $s->tenant?->name,
            'status' => $s->status,
            'status_label' => $s->statusLabel(),
            'type' => $s->destination_type,
            'type_label' => Stand::typeOptions()[$s->destination_type] ?? $s->destination_type,
            'encoded_at' => $s->encoded_at?->toIso8601String(),
            'encoded_kind' => $s->encoded_kind,
            'public_url' => $s->publicUrl(),
            'payload' => $s->nfcPayload(),
        ];
    }
}
