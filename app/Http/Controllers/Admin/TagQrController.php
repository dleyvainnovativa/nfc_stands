<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stand;
use App\Services\Qr;

/**
 * Super-admin: a print-ready sheet of blank (unclaimed) tags — each cell shows a
 * QR of the tag URL plus its code — to print and stick next to / on the NFC
 * sticker during production. Scanning the QR hits the same /t/{code} as a tap.
 */
class TagQrController extends Controller
{
    public function sheet()
    {
        $tags = Stand::withoutTenantScope()
            ->whereNull('tenant_id')
            ->where('status', Stand::STATUS_UNASSIGNED)
            ->orderByDesc('id')
            ->limit(300) // keep one sheet sane; generate more in batches if needed
            ->get(['id', 'code']);

        $cells = $tags->map(fn ($t) => [
            'code' => $t->code,
            'url' => url('/t/' . $t->code),
            'svg' => Qr::svg(url('/t/' . $t->code), 140, 1),
        ]);

        return view('admin.tags_qr_sheet', ['cells' => $cells]);
    }
}
