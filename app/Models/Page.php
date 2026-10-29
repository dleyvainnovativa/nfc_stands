<?php

namespace App\Models;

use App\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * A "linktree"-style micro-page owned by a tenant. The admin creates it and
 * controls publishing (is_active); the client edits its content and styling.
 * Reachable at /p/{code}, and a stand can point to it.
 */
class Page extends Model
{
    use HasTenant;

    public const DEFAULT_PRIMARY = '#2e6be6';
    public const DEFAULT_SECONDARY = '#1b34c4';

    protected $fillable = [
        'tenant_id', 'code', 'name', 'title', 'description', 'logo_path',
        'primary_color', 'secondary_color', 'button_shape', 'button_fill', 'bg_style',
        'links', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'links' => 'array',
            'is_active' => 'boolean',
            'views' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            if (empty($page->code)) {
                $page->code = static::uniqueCode();
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

    public function linkClicks(): HasMany
    {
        return $this->hasMany(PageLinkClick::class);
    }

    /* ---- Display ---- */
    public function displayTitle(): string
    {
        return $this->title ?: $this->name;
    }

    public function publicUrl(): string
    {
        return url('/p/' . $this->code);
    }

    public function statusLabel(): string
    {
        return $this->is_active ? 'Publicada' : 'Oculta';
    }

    public function brandPrimary(): string
    {
        return $this->primary_color ?: self::DEFAULT_PRIMARY;
    }

    public function brandSecondary(): string
    {
        return $this->secondary_color ?: self::DEFAULT_SECONDARY;
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    /** Clean link list: only entries with a URL. */
    public function cleanLinks(): array
    {
        return collect($this->links ?? [])
            ->filter(fn ($l) => filled($l['url'] ?? null))
            ->map(fn ($l) => [
                'label' => $l['label'] ?? $l['url'],
                'url'   => $l['url'],
                'icon'  => $l['icon'] ?? null,
            ])
            ->values()
            ->all();
    }

    /* ---- Option lists ---- */
    public static function buttonShapeOptions(): array
    {
        return ['rounded' => 'Redondeado', 'pill' => 'Píldora', 'square' => 'Cuadrado'];
    }

    public static function buttonFillOptions(): array
    {
        return ['solid' => 'Relleno', 'outline' => 'Contorno'];
    }

    public static function bgStyleOptions(): array
    {
        return ['gradient' => 'Degradado', 'plain' => 'Sólido'];
    }

    /** Curated Font Awesome icons the client can attach to a link. */
    public static function iconOptions(): array
    {
        return [
            '' => 'Sin icono',
            'fa-solid fa-globe' => 'Sitio web',
            'fa-solid fa-utensils' => 'Menú',
            'fa-solid fa-star' => 'Reseñas',
            'fa-solid fa-location-dot' => 'Ubicación',
            'fa-solid fa-phone' => 'Teléfono',
            'fa-solid fa-envelope' => 'Correo',
            'fa-solid fa-calendar' => 'Reservar',
            'fa-solid fa-cart-shopping' => 'Tienda',
            'fa-brands fa-whatsapp' => 'WhatsApp',
            'fa-brands fa-instagram' => 'Instagram',
            'fa-brands fa-facebook' => 'Facebook',
            'fa-brands fa-tiktok' => 'TikTok',
            'fa-brands fa-x-twitter' => 'X (Twitter)',
            'fa-brands fa-youtube' => 'YouTube',
            'fa-brands fa-spotify' => 'Spotify',
            'fa-solid fa-link' => 'Enlace',
        ];
    }
}
