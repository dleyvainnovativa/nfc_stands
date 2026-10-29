<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Survey extends Model
{
    use HasTenant;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id', 'code', 'title', 'description', 'status', 'thank_you_message',
        'paginated', 'collect_contact', 'return_url', 'return_label', 'font_family',
    ];

    protected function casts(): array
    {
        return [
            'paginated' => 'boolean',
            'collect_contact' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Survey $survey) {
            if (empty($survey->code)) {
                $survey->code = static::uniqueCode();
            }
        });
    }

    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function uniqueCode(int $len = 8): string
    {
        do {
            $code = '';
            $max = strlen(self::ALPHABET) - 1;
            for ($i = 0; $i < $len; $i++) {
                $code .= self::ALPHABET[random_int(0, $max)];
            }
        } while (static::withoutTenantScope()->where('code', $code)->exists());
        return $code;
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('position')->orderBy('id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function publicUrl(): string
    {
        return url('/s/' . $this->code);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_CLOSED => 'Cerrada',
            default => ucfirst($this->status),
        };
    }
}
