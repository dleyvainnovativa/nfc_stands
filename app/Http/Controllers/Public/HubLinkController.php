<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HubLinkClick;
use App\Models\Stand;
use App\Support\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Click-through tracker for hub links: GET /t/{code}/go/{i}
 *
 * Hub buttons point here instead of straight out. We log which link was tapped,
 * then redirect to the real URL. Same cheap hot path as a scan: one lookup +
 * one insert, logging wrapped so it can never block the redirect.
 */
class HubLinkController extends Controller
{
    public function go(Request $request, string $code, int $i)
    {
        $stand = Stand::withoutTenantScope()->where('code', $code)->first();

        // Anything off (missing, not active, not a hub) → send them to the tag's
        // normal resolution rather than erroring.
        if (! $stand || ! $stand->isActive() || $stand->destination_type !== Stand::TYPE_HUB) {
            return redirect()->to(url('/t/' . $code));
        }

        $links = $stand->destination_config['links'] ?? [];
        $link = $links[$i] ?? null;

        if (! $link || empty($link['url'])) {
            return redirect()->to(url('/t/' . $code));
        }

        $this->logClick($request, $stand, $i, $link);

        return redirect()->away($link['url'], 302);
    }

    private function logClick(Request $request, Stand $stand, int $i, array $link): void
    {
        try {
            HubLinkClick::create([
                'tenant_id' => $stand->tenant_id,
                'stand_id' => $stand->id,
                'link_index' => $i,
                'label' => $link['label'] ?? null,
                'url' => mb_substr($link['url'], 0, 2048),
                'occurred_at' => Carbon::now(),
                'device' => Device::detect($request->userAgent()),
                'ip_hash' => Device::hashIp($request->ip()),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
