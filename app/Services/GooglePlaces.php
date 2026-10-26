<?php

namespace App\Services;

use App\Models\GooglePlace;
use App\Models\GooglePlaceDailyStat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Places API (New) — key-only Place Details.
 *
 * Returns the aggregate rating, total review count, and up to 5 latest reviews
 * for ANY place by its place ID. No OAuth, no per-location ownership, no access
 * application (that's the Business Profile API, a future premium tier).
 *
 * We persist place IDs indefinitely (allowed) and store our own daily snapshots
 * of rating + count for the trend; Google's review text is only ever the latest
 * five, refreshed in place.
 */
class GooglePlaces
{
    /** Fetch + normalize one place from the API. @throws RuntimeException */
    public function fetch(string $placeId): array
    {
        $key = config('googleplaces.key');
        if (empty($key)) {
            throw new RuntimeException('GOOGLE_PLACES_API_KEY is not configured.');
        }

        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $key,
            'X-Goog-FieldMask' => config('googleplaces.field_mask'),
        ])
            ->timeout((int) config('googleplaces.timeout', 10))
            ->acceptJson()
            ->get(config('googleplaces.endpoint') . rawurlencode($placeId));

        if (! $response->successful()) {
            $msg = $response->json('error.message') ?? ('HTTP ' . $response->status());
            throw new RuntimeException('Google Places error: ' . $msg);
        }

        return $this->parse($response->json() ?? []);
    }

    /**
     * Normalize a Places API (New) Place payload into our shape.
     * Pure function — no I/O — so it can be verified against a sample response.
     */
    public function parse(array $json): array
    {
        $reviews = [];
        foreach (($json['reviews'] ?? []) as $rv) {
            $reviews[] = [
                'author' => $rv['authorAttribution']['displayName'] ?? 'Anonymous',
                'rating' => isset($rv['rating']) ? (int) $rv['rating'] : null,
                // Prefer the review in its ORIGINAL language over Google's translation.
                'text' => $rv['originalText']['text'] ?? ($rv['text']['text'] ?? ''),
                'relative_time' => $rv['relativePublishTimeDescription'] ?? null,
                'published_at' => $rv['publishTime'] ?? null,
            ];
        }

        return [
            'name' => $json['displayName']['text'] ?? null,
            'rating' => isset($json['rating']) ? (float) $json['rating'] : null,
            'user_rating_count' => isset($json['userRatingCount']) ? (int) $json['userRatingCount'] : null,
            'maps_uri' => $json['googleMapsUri'] ?? null,
            'reviews' => $reviews,
        ];
    }

    /**
     * Sync one stored place: fetch, update the row, and upsert today's snapshot.
     * Returns true on success; on failure records sync_error and returns false.
     */
    public function sync(GooglePlace $place): bool
    {
        try {
            $data = $this->fetch($place->place_id);
        } catch (\Throwable $e) {
            $place->forceFill([
                'sync_error' => mb_substr($e->getMessage(), 0, 255),
                'last_synced_at' => Carbon::now(),
            ])->save();
            report($e);
            return false;
        }

        $place->forceFill([
            'name' => $data['name'] ?? $place->name,
            'rating' => $data['rating'],
            'user_rating_count' => $data['user_rating_count'],
            'maps_uri' => $data['maps_uri'] ?? $place->maps_uri,
            'reviews' => $data['reviews'],
            'last_synced_at' => Carbon::now(),
            'sync_error' => null,
        ])->save();

        // Today's snapshot (idempotent on re-run).
        GooglePlaceDailyStat::updateOrCreate(
            ['google_place_id' => $place->id, 'stat_date' => Carbon::today()->toDateString()],
            [
                'tenant_id' => $place->tenant_id,
                'rating' => $data['rating'],
                'user_rating_count' => $data['user_rating_count'],
            ]
        );

        return true;
    }
}
