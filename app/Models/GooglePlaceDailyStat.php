<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GooglePlaceDailyStat extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'google_place_id', 'stat_date', 'rating', 'user_rating_count',
    ];

    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'rating' => 'decimal:1',
            'user_rating_count' => 'integer',
        ];
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(GooglePlace::class, 'google_place_id');
    }
}
