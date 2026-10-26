<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandDailyStat extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'stand_id', 'stat_date', 'scan_count', 'unique_devices',
    ];

    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'scan_count' => 'integer',
            'unique_devices' => 'integer',
        ];
    }

    public function stand(): BelongsTo
    {
        return $this->belongsTo(Stand::class);
    }
}
