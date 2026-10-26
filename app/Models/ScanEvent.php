<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanEvent extends Model
{
    use HasTenant;

    // Immutable events: created_at only, no updated_at.
    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id', 'stand_id', 'occurred_at',
        'device', 'ip_hash', 'referer', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function stand(): BelongsTo
    {
        return $this->belongsTo(Stand::class);
    }
}
