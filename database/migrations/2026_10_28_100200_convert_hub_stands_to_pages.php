<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The inline "Link hub" stand type is replaced by standalone Pages. Convert each
 * existing hub stand into a Page (carrying its title + links) and repoint the
 * stand's destination at that page. Raw DB only, so it's independent of model code.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();

        $hubs = DB::table('stands')->where('destination_type', 'hub')->get();

        foreach ($hubs as $stand) {
            if ($stand->tenant_id === null) {
                // Orphan hub with no owner — just neutralise the dead type.
                DB::table('stands')->where('id', $stand->id)->update([
                    'destination_type' => 'direct',
                    'destination_config' => json_encode(['url' => '']),
                ]);
                continue;
            }

            $cfg = json_decode($stand->destination_config ?? '{}', true) ?: [];
            $title = $cfg['title'] ?? ($stand->label ?: 'Enlaces');

            $links = [];
            foreach (($cfg['links'] ?? []) as $l) {
                if (! empty($l['url'])) {
                    $links[] = [
                        'label' => $l['label'] ?? $l['url'],
                        'url'   => $l['url'],
                        'icon'  => null,
                    ];
                }
            }

            do {
                $code = Str::random(8);
            } while (DB::table('pages')->where('code', $code)->exists());

            $pageId = DB::table('pages')->insertGetId([
                'tenant_id'       => $stand->tenant_id,
                'code'            => $code,
                'name'            => $title,
                'title'           => $title,
                'description'     => null,
                'logo_path'       => null,
                'primary_color'   => null,
                'secondary_color' => null,
                'button_shape'    => 'rounded',
                'button_fill'     => 'solid',
                'bg_style'        => 'gradient',
                'links'           => json_encode($links),
                'views'           => 0,
                'is_active'       => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            DB::table('stands')->where('id', $stand->id)->update([
                'destination_type'   => 'page',
                'destination_config' => json_encode(['page_id' => $pageId, 'page_code' => $code]),
            ]);
        }
    }

    public function down(): void
    {
        // One-way data migration; pages created here are kept.
    }
};
