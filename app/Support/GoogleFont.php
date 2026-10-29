<?php

namespace App\Support;

/**
 * Curated Google Fonts offered on public pages and surveys.
 *
 * Fonts load straight from Google's CSS API at render time — nothing is stored
 * or uploaded. The family is chosen from this whitelist (keys), so the value is
 * safe to drop into a URL and a font-family stack. Each family carries the exact
 * weights it actually ships, because asking css2 for a weight a font doesn't
 * have returns an error and the font silently fails to load.
 */
class GoogleFont
{
    /** family => available weights (as the css2 `wght@` list). */
    private const FONTS = [
        'Inter' => '400;500;600;700',
        'Poppins' => '400;500;600;700',
        'Montserrat' => '400;500;600;700',
        'Roboto' => '400;500;700',
        'Open Sans' => '400;600;700',
        'Lato' => '400;700',
        'Nunito' => '400;600;700',
        'Raleway' => '400;500;600;700',
        'Work Sans' => '400;500;600;700',
        'DM Sans' => '400;500;700',
        'Quicksand' => '400;500;600;700',
        'Oswald' => '400;500;600;700',
        'Bebas Neue' => '400',
        'Playfair Display' => '400;500;600;700',
        'Merriweather' => '400;700',
        'Lora' => '400;500;600;700',
        'Cormorant Garamond' => '400;500;600;700',
        'Pacifico' => '400',
        'Dancing Script' => '400;500;600;700',
        'Lobster' => '400',
        'Caveat' => '400;500;600;700',
        'Abril Fatface' => '400',
        'Comfortaa' => '400;500;600;700',
        'Sacramento' => '400',
    ];

    /** Select options for the editors: '' (default) + each family. */
    public static function options(): array
    {
        $out = ['' => 'Predeterminada (Inter)'];
        foreach (array_keys(self::FONTS) as $family) {
            $out[$family] = $family;
        }
        return $out;
    }

    public static function isValid(?string $family): bool
    {
        return $family !== null && $family !== '' && array_key_exists($family, self::FONTS);
    }

    /** Google Fonts css2 URL for one family (with its real weights), or null. */
    public static function cssUrl(?string $family): ?string
    {
        if (! self::isValid($family)) {
            return null;
        }
        $name = str_replace('%20', '+', rawurlencode($family));
        return "https://fonts.googleapis.com/css2?family={$name}:wght@" . self::FONTS[$family] . '&display=swap';
    }

    /** CSS font-family stack with a safe fallback chain. */
    public static function stack(?string $family): string
    {
        if (! self::isValid($family)) {
            return 'Inter, system-ui, -apple-system, sans-serif';
        }
        return "'{$family}', Inter, system-ui, -apple-system, sans-serif";
    }

    /** family => cssUrl for every selectable font (used to lazy-load previews). */
    public static function urlMap(): array
    {
        $map = [];
        foreach (array_keys(self::FONTS) as $family) {
            $map[$family] = self::cssUrl($family);
        }
        return $map;
    }
}
