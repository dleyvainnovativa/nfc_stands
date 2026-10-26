<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stand;
use App\Support\StandCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Super-admin: generate a batch of blank, unassigned tags to be encoded onto
 * physical NFC stickers. Each gets a unique code; they stay tenant-less and
 * "unassigned" until a client claims one by its code.
 *
 * Export the codes as CSV to hand to whoever encodes/prints the tags.
 */
class TagBatchController extends Controller
{
    public function index()
    {
        $blanks = Stand::withoutTenantScope()
            ->whereNull('tenant_id')
            ->where('status', Stand::STATUS_UNASSIGNED)
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.tags', compact('blanks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $codes = StandCode::uniqueBatch($data['count']);
        $now = Carbon::now();

        $rows = array_map(fn ($code) => [
            'tenant_id' => null,
            'code' => $code,
            'status' => Stand::STATUS_UNASSIGNED,
            'destination_type' => Stand::TYPE_DIRECT,
            'destination_config' => null,
            'scans_total' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $codes);

        // Bulk insert bypasses model events, which is fine — codes are pre-made
        // unique and there's no tenant to auto-fill on a blank tag.
        DB::table('stands')->insert($rows);

        return redirect()->route('admin.tags.index')
            ->with('flash.success', count($codes) . ' etiquetas en blanco generadas.')
            ->with('generated_codes', $codes);
    }

    public function export(Request $request)
    {
        $codes = Stand::withoutTenantScope()
            ->whereNull('tenant_id')
            ->where('status', Stand::STATUS_UNASSIGNED)
            ->orderBy('id')
            ->pluck('code');

        $base = rtrim(config('app.url'), '/') . '/t/';
        $filename = 'blank-tags-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($codes, $base) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['code', 'url']);
            foreach ($codes as $code) {
                fputcsv($out, [$code, $base . $code]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
