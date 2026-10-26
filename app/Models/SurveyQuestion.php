<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    use HasTenant;

    public const TYPE_SINGLE = 'single';
    public const TYPE_MULTI = 'multi';
    public const TYPE_RATING = 'rating';
    public const TYPE_NPS = 'nps';
    public const TYPE_TEXT = 'text';

    protected $fillable = [
        'tenant_id', 'survey_id', 'type', 'prompt', 'options', 'config', 'required', 'position',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'config' => 'array',
            'required' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    public function isChoice(): bool
    {
        return in_array($this->type, [self::TYPE_SINGLE, self::TYPE_MULTI], true);
    }

    public function ratingMax(): int
    {
        return (int) ($this->config['max'] ?? 5);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_SINGLE => 'Opción única',
            self::TYPE_MULTI => 'Opción múltiple',
            self::TYPE_RATING => 'Calificación',
            self::TYPE_NPS => 'NPS (0–10)',
            self::TYPE_TEXT => 'Texto abierto',
        ];
    }
}
