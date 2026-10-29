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
    public const DEFAULT_BG = '#f4f5f7';

    protected $fillable = [
        'tenant_id', 'code', 'name', 'title', 'description', 'logo_path',
        'primary_color', 'secondary_color', 'button_shape', 'button_fill', 'bg_style',
        'bg_color', 'text_mode', 'links', 'is_active',
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

    public function bgColor(): string
    {
        return $this->bg_color ?: self::DEFAULT_BG;
    }

    /** Pick black or white text for the best contrast on a hex colour (YIQ). */
    public static function contrastColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return '#1e2733';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $yiq = ($r * 299 + $g * 587 + $b * 114) / 1000;

        return $yiq >= 150 ? '#1e2733' : '#ffffff';
    }

    /** Average two hex colours channel-by-channel. */
    public static function blendColors(string $a, string $b): string
    {
        $a = ltrim($a, '#');
        $b = ltrim($b, '#');
        if (strlen($a) !== 6 || strlen($b) !== 6) {
            return '#' . ($a ?: ($b ?: '888888'));
        }
        $mix = fn ($i) => str_pad(dechex((int) ((hexdec(substr($a, $i, 2)) + hexdec(substr($b, $i, 2))) / 2)), 2, '0', STR_PAD_LEFT);

        return '#' . $mix(0) . $mix(2) . $mix(4);
    }

    /** The representative background colour used for contrast decisions. */
    public function bgReference(): string
    {
        return $this->bg_style === 'plain'
            ? $this->bgColor()
            : self::blendColors($this->brandPrimary(), $this->brandSecondary());
    }

    /** Page text colour, honouring the chosen text mode. */
    public function textColor(): string
    {
        return match ($this->text_mode) {
            'light' => '#ffffff',
            'dark'  => '#1e2733',
            default => self::contrastColor($this->bgReference()),
        };
    }

    /** Readable text/icon colour on a primary-filled button. */
    public function onPrimary(): string
    {
        return self::contrastColor($this->brandPrimary());
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
                'bg'    => $l['bg'] ?? null,
                'text'  => $l['text'] ?? null,
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

    public static function textModeOptions(): array
    {
        return ['auto' => 'Automático', 'light' => 'Claro', 'dark' => 'Oscuro'];
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
