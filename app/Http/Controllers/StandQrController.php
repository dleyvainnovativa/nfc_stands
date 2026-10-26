<?php

namespace App\Http\Controllers;

use App\Models\Stand;
use App\Services\Qr;
use Illuminate\Http\Request;

class StandQrController extends Controller
{
    /** Raw SVG for a stand's tag URL. ?dl=1 forces a download. */
    public function svg(Request $request, Stand $stand)
    {
        // Model binding respects the tenant scope, so clients only reach their own.
        $svg = Qr::svg($stand->publicUrl(), 320, 2);

        $headers = ['Content-Type' => 'image/svg+xml'];
        if ($request->boolean('dl')) {
            $headers['Content-Disposition'] = 'attachment; filename="stand-' . $stand->code . '.svg"';
        } else {
            // Inline images can be cached by the browser for a bit.
            $headers['Cache-Control'] = 'private, max-age=600';
        }

        return response($svg, 200, $headers);
    }

    /** A clean, printable single-stand page (QR + code + label). */
    public function print(Stand $stand)
    {
        $svg = Qr::svg($stand->publicUrl(), 340, 2);
        return view('stands.qr_print', compact('stand', 'svg'));
    }
}
