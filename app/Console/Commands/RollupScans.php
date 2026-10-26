<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Rolls raw scan_events into stand_daily_stats and refreshes the denormalized
 * counters on stands (scans_total, last_scan_at).
 *
 * Re-runnable and idempotent: it recomputes a short trailing window each run
 * (default: today + yesterday) and upserts on (stand_id, stat_date), so today's
 * partial day keeps updating and yesterday finalizes. Scheduled nightly, but
 * safe to run on demand.
 *
 *   php artisan stands:rollup-scans
 *   php artisan stands:rollup-scans --days=7          # re-aggregate last 7 days
 *   php artisan stands:rollup-scans --date=2026-10-15 # a single day
 */
class RollupScans extends Command
{
    protected $signature = 'stands:rollup-scans
                            {--days=2 : Trailing days to recompute (ignored if --date is set)}
                            {--date=  : Recompute a single YYYY-MM-DD day}';

    protected $description = 'Aggregate scan events into daily stats and refresh stand counters';

    public function handle(): int
    {
        [$from, $to] = $this->window();
        $this->info("Rolling up scans {$from->toDateString()} → {$to->toDateString()}");

        // 1) Aggregate events in the window, grouped per stand per day.
        $grouped = DB::table('scan_events')
            ->selectRaw('stand_id, tenant_id, DATE(occurred_at) as stat_date,
                         COUNT(*) as scan_count,
                         COUNT(DISTINCT ip_hash) as unique_devices')
            ->whereBetween('occurred_at', [$from->startOfDay(), $to->endOfDay()])
            ->groupBy('stand_id', 'tenant_id', DB::raw('DATE(occurred_at)'))
            ->get();

        $now = Carbon::now();
        $affectedStandIds = [];

        // 2) Upsert daily rows in chunks (shared-hosting friendly).
        foreach ($grouped->chunk(500) as $chunk) {
            $rows = $chunk->map(function ($r) use ($now, &$affectedStandIds) {
                $affectedStandIds[$r->stand_id] = true;
                return [
                    'tenant_id' => $r->tenant_id,
                    'stand_id' => $r->stand_id,
                    'stat_date' => $r->stat_date,
                    'scan_count' => $r->scan_count,
                    'unique_devices' => $r->unique_devices,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            if ($rows) {
                DB::table('stand_daily_stats')->upsert(
                    $rows,
                    ['stand_id', 'stat_date'],                 // unique key
                    ['tenant_id', 'scan_count', 'unique_devices', 'updated_at']
                );
            }
        }

        // 3) Refresh denormalized counters on the stands that had activity.
        $ids = array_keys($affectedStandIds);
        $this->refreshStandCounters($ids);

        $this->info('Done. Stands touched: ' . count($ids) . '. Daily rows: ' . $grouped->count() . '.');
        return self::SUCCESS;
    }

    /** @param array<int> $standIds */
    private function refreshStandCounters(array $standIds): void
    {
        foreach (array_chunk($standIds, 200) as $chunk) {
            // Total from the finalized daily table (cheap, indexed).
            $totals = DB::table('stand_daily_stats')
                ->select('stand_id', DB::raw('SUM(scan_count) as total'))
                ->whereIn('stand_id', $chunk)
                ->groupBy('stand_id')
                ->pluck('total', 'stand_id');

            // Last scan straight from events (authoritative to the second).
            $lastScans = DB::table('scan_events')
                ->select('stand_id', DB::raw('MAX(occurred_at) as last_at'))
                ->whereIn('stand_id', $chunk)
                ->groupBy('stand_id')
                ->pluck('last_at', 'stand_id');

            foreach ($chunk as $id) {
                DB::table('stands')->where('id', $id)->update([
                    'scans_total' => (int) ($totals[$id] ?? 0),
                    'last_scan_at' => $lastScans[$id] ?? null,
                ]);
            }
        }
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function window(): array
    {
        if ($date = $this->option('date')) {
            $d = Carbon::parse($date);
            return [$d->copy(), $d->copy()];
        }

        $days = max(1, (int) $this->option('days'));
        return [Carbon::today()->subDays($days - 1), Carbon::today()];
    }
}
