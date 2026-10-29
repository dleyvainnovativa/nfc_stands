<?php

namespace App\Models;

use App\Concerns\HasTenant;
use App\Support\StandCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stand extends Model
{
    use HasTenant;

    public const STATUS_UNASSIGNED = 'unassigned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';

    public const TYPE_DIRECT = 'direct';
    public const TYPE_PAGE = 'page';
    public const TYPE_SURVEY = 'survey';
    public const TYPE_REVIEWS = 'reviews';
    public const TYPE_WIFI = 'wifi';

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
            'encoded_at' => 'datetime',
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

    public function encodedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isClaimable(): bool
    {
        return $this->status === self::STATUS_UNASSIGNED && $this->tenant_id === null;
    }

    /** Whether a physical tag has been written for this code. */
    public function isEncoded(): bool
    {
        return $this->encoded_at !== null;
    }

    /**
     * ['kind'=>'redirect','url'=>..] | ['kind'=>'hub']
     * | ['kind'=>'survey','survey_code'=>..] | null
     * (reviews resolves to a plain redirect to the write-review URL.)
     *
     * WiFi stands have no server-resolved destination: the credential lives on
     * the tag itself, so a /t/{code} hit on a WiFi tag falls through to null.
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
            self::TYPE_PAGE => filled($cfg['page_code'] ?? null)
                ? ['kind' => 'redirect', 'url' => url('/p/' . $cfg['page_code'])]
                : null,
            self::TYPE_SURVEY => filled($cfg['survey_code'] ?? null)
                ? ['kind' => 'survey', 'survey_code' => $cfg['survey_code']]
                : null,
            default => null, // wifi + anything unknown
        };
    }

    /**
     * What the NFC writer should physically burn onto the tag:
     *  - url  : the permanent /t/{code} short URL (every type except wifi)
     *  - wifi : the raw Wi-Fi credential, written as a WSC NDEF record
     */
    public function nfcPayload(): array
    {
        if ($this->destination_type === self::TYPE_WIFI) {
            $cfg = $this->destination_config ?? [];
            return [
                'kind' => 'wifi',
                'ssid' => $cfg['ssid'] ?? '',
                'password' => $cfg['password'] ?? '',
                'auth' => $cfg['auth'] ?? 'WPA2',   // WPA2 | WPA_WPA2 | OPEN
                'hidden' => (bool) ($cfg['hidden'] ?? false),
            ];
        }

        return ['kind' => 'url', 'url' => $this->publicUrl()];
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
            self::TYPE_PAGE => 'Página (linktree)',
            self::TYPE_SURVEY => 'Encuesta',
            self::TYPE_REVIEWS => 'Reseña de Google',
            self::TYPE_WIFI => 'Red WiFi',
        ];
    }
}
