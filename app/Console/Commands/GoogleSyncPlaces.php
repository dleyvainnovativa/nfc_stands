<?php

namespace App\Console\Commands;

use App\Models\GooglePlace;
use App\Services\GooglePlaces;
use Illuminate\Console\Command;

/**
 * Daily sync of all stored Google places → latest rating/count/reviews + a
 * dated snapshot for the trend. Runs across all tenants (no tenant scope).
 *
 *   php artisan google:sync-places
 *   php artisan google:sync-places --place=42
 */
class GoogleSyncPlaces extends Command
{
    protected $signature = 'google:sync-places {--place= : Sync only this google_places id}';
    protected $description = 'Sync Google Places ratings/reviews and record daily snapshots';

    public function handle(GooglePlaces $service): int
    {
        $query = GooglePlace::withoutTenantScope();
        if ($id = $this->option('place')) {
            $query->whereKey($id);
        }

        $places = $query->get();
        if ($places->isEmpty()) {
            $this->info('No places to sync.');
            return self::SUCCESS;
        }

        $ok = 0; $fail = 0;
        foreach ($places as $place) {
            $success = $service->sync($place);
            $success ? $ok++ : $fail++;
            $this->line(($success ? '  ✓ ' : '  ✗ ') . $place->place_id
                . ($success ? " (rating {$place->rating}, {$place->user_rating_count})" : " — {$place->sync_error}"));

            // Be gentle with the API / avoid bursts on shared hosting.
            usleep(200_000);
        }

        $this->info("Synced {$ok} place(s), {$fail} failed.");
        return self::SUCCESS;
    }
}
