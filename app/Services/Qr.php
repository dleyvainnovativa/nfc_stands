<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * QR generation via bacon/qr-code's SVG backend.
 *
 * SVG output is pure PHP — no GD/Imagick extension needed (important on Hostinger
 * shared hosting) — and vector, so it prints crisp at any size. We QR-encode the
 * stand's PERMANENT short URL (/t/{code}), never the destination, so the printed
 * code keeps working after the destination is repointed.
 *
 * Requires: composer require bacon/qr-code
 */
class Qr
{
    public static function svg(string $data, int $size = 280, int $margin = 2): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, $margin),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($data);
    }
}
