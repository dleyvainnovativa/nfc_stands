<?php

namespace App\Models;

use App\Concerns\HasTenant;
use App\Support\StandCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stand extends Model
{
    use HasTenant;

    public const STATUS_UNASSIGNED = 'unassigned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';

    public const TYPE_DIRECT = 'direct';
    public const TYPE_HUB = 'hub';
    public const TYPE_SURVEY = 'survey';
    public const TYPE_REVIEWS = 'reviews';

    protected $fillable = [
        'tenant_id', 'code', 'label', 'status',
        'destination_type', 'destination_config',
        'place_id', 'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'destination_config' => 'array',
            'claimed_at' => 'datetime',
            'last_scan_at' => 'datetime',
            'scans_total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Stand $stand) {
            if (empty($stand->code)) {
                $stand->code = StandCode::unique();
            }
        });
    }

    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(StandDailyStat::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isClaimable(): bool
    {
        return $this->status === self::STATUS_UNASSIGNED && $this->tenant_id === null;
    }

    /**
     * ['kind'=>'redirect','url'=>..] | ['kind'=>'hub']
     * | ['kind'=>'survey','survey_code'=>..] | null
     * (reviews resolves to a plain redirect to the write-review URL.)
     */
    public function resolveDestination(): ?array
    {
        if (! $this->isActive()) {
            return null;
        }

        $cfg = $this->destination_config ?? [];

        return match ($this->destination_type) {
            self::TYPE_DIRECT => filled($cfg['url'] ?? null)
                ? ['kind' => 'redirect', 'url' => $cfg['url']]
                : null,
            self::TYPE_REVIEWS => filled($cfg['review_url'] ?? null)
                ? ['kind' => 'redirect', 'url' => $cfg['review_url']]
                : null,
            self::TYPE_HUB => ['kind' => 'hub'],
            self::TYPE_SURVEY => filled($cfg['survey_code'] ?? null)
                ? ['kind' => 'survey', 'survey_code' => $cfg['survey_code']]
                : null,
            default => null,
        };
    }

    public function publicUrl(): string
    {
        return url('/t/' . $this->code);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_DISABLED => 'Deshabilitado',
            default => 'Sin asignar',
        };
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DIRECT => 'Enlace directo',
            self::TYPE_HUB => 'Hub de enlaces',
            self::TYPE_SURVEY => 'Encuesta',
            self::TYPE_REVIEWS => 'Reseña de Google',
        ];
    }
}
