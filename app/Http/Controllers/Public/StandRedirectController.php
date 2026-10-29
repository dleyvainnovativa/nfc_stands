<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ScanEvent;
use App\Models\Stand;
use App\Support\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Public endpoint for a physical tag: GET /t/{code}
 *
 * One indexed lookup + one cheap insert, then redirect / render. Counters are
 * maintained off the hot path by the nightly rollup.
 */
class StandRedirectController extends Controller
{
    public function show(Request $request, string $code)
    {
        $stand = Stand::withoutTenantScope()->where('code', $code)->first();

        if (! $stand) {
            return response()->view('public.inactive', ['reason' => 'not_found'], 404);
        }

        $destination = $stand->resolveDestination();

        if ($destination === null) {
            return response()->view('public.inactive', [
                'reason' => $stand->isClaimable() ? 'unassigned' : 'inactive',
                'stand' => $stand,
            ], 200);
        }

        $this->logScan($request, $stand);

        return match ($destination['kind']) {
            'redirect' => redirect()->away($destination['url'], 302),
            'survey'   => redirect()->to(
                url('/s/' . $destination['survey_code']) . '?via=' . $stand->code, 302
            ),
            default    => response()->view('public.inactive', ['reason' => 'inactive'], 200),
        };
    }

    private function logScan(Request $request, Stand $stand): void
    {
        try {
            ScanEvent::create([
                'tenant_id'  => $stand->tenant_id,
                'stand_id'   => $stand->id,
                'occurred_at' => Carbon::now(),
                'device'     => Device::detect($request->userAgent()),
                'ip_hash'    => Device::hashIp($request->ip()),
                'referer'    => $request->headers->get('referer'),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
            ]);
        } catch (\Throwable $e) {
            report($e); // never let analytics break the redirect
        }
    }
}
