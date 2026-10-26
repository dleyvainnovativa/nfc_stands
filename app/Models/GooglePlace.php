<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class GooglePlace extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'place_id', 'label', 'name',
        'rating', 'user_rating_count', 'maps_uri', 'reviews',
        'last_synced_at', 'sync_error',
    ];

    protected function casts(): array
    {
        return [
            'reviews' => 'array',
            'rating' => 'decimal:1',
            'user_rating_count' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(GooglePlaceDailyStat::class);
    }

    public function displayName(): string
    {
        return $this->label ?: ($this->name ?: $this->place_id);
    }

    /** Public "write a review" deep link — no API needed. */
    public function writeReviewUrl(): string
    {
        return config('googleplaces.write_review_url') . $this->place_id;
    }

    /** Rating + count trend for a chart. Zero/last-value filled over N days. */
    public function trend(int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $rows = $this->dailyStats()
            ->where('stat_date', '>=', $start->toDateString())
            ->orderBy('stat_date')
            ->get(['stat_date', 'rating', 'user_rating_count'])
            ->keyBy(fn ($r) => $r->stat_date->toDateString());

        $labels = []; $ratings = []; $counts = [];
        $lastRating = null; $lastCount = null;
        for ($d = 0; $d < $days; $d++) {
            $date = $start->copy()->addDays($d)->toDateString();
            $labels[] = $date;
            if (isset($rows[$date])) {
                $lastRating = $rows[$date]->rating !== null ? (float) $rows[$date]->rating : $lastRating;
                $lastCount = $rows[$date]->user_rating_count ?? $lastCount;
            }
            // Carry the last known value forward so the line doesn't dip to 0 on gaps.
            $ratings[] = $lastRating;
            $counts[] = $lastCount;
        }

        return ['labels' => $labels, 'ratings' => $ratings, 'counts' => $counts];
    }
}
