<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubLinkClick extends Model
{
    use HasTenant;

    public const UPDATED_AT = null; // immutable event

    protected $fillable = [
        'tenant_id', 'stand_id', 'link_index', 'label', 'url', 'occurred_at', 'device', 'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'link_index' => 'integer',
        ];
    }

    public function stand(): BelongsTo
    {
        return $this->belongsTo(Stand::class);
    }
}
