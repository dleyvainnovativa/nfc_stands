<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'contact_email',
        'contact_phone',
        'is_active',
        'primary_color',
        'secondary_color',
        'logo_path',
    ];

    /** Fallback brand colours (Complemento blue) when a tenant sets none. */
    public const DEFAULT_PRIMARY = '#2e6be6';
    public const DEFAULT_SECONDARY = '#1b34c4';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = static::uniqueSlug($tenant->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'tenant';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function googlePlaces(): HasMany
    {
        return $this->hasMany(GooglePlace::class);
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /** Display helper for the brand/logo mark colour. */
    public function initial(): string
    {
        return mb_strtoupper(mb_substr(trim($this->name), 0, 1)) ?: '?';
    }

    /* ---- Branding ---- */
    public function brandPrimary(): string
    {
        return $this->primary_color ?: self::DEFAULT_PRIMARY;
    }

    public function brandSecondary(): string
    {
        return $this->secondary_color ?: self::DEFAULT_SECONDARY;
    }

    public function hasCustomBrand(): bool
    {
        return filled($this->primary_color) || filled($this->secondary_color) || filled($this->logo_path);
    }

    /** Public URL of the uploaded logo, or null. Requires `php artisan storage:link`. */
    public function logoUrl(): ?string
    {
        return $this->logo_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo_path)
            : null;
    }
}
