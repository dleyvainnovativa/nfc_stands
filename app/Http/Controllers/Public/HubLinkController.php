<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

/**
 * Legacy hub click tracker. The inline "Link hub" stand type was replaced by
 * standalone Pages (see Public\PageController), so this is no longer routed.
 * Kept as a harmless redirect in case an old link is hit.
 */
class HubLinkController extends Controller
{
    public function go(string $code, int $i)
    {
        return redirect()->to(url('/t/' . $code));
    }
}
