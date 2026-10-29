<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageLinkClick;
use App\Support\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Public linktree page: GET /p/{code} renders it, GET /p/{code}/go/{i} tracks a
 * link tap then redirects out. Only published (is_active) pages are shown.
 */
class PageController extends Controller
{
    public function show(string $code)
    {
        $page = Page::withoutTenantScope()->where('code', $code)->first();

        if (! $page || ! $page->is_active) {
            return response()->view('public.page_unavailable', [], $page ? 200 : 404);
        }

        // Cheap, atomic view counter.
        try {
            Page::withoutTenantScope()->whereKey($page->id)->increment('views');
        } catch (\Throwable $e) {
            report($e);
        }

        return view('public.page', ['page' => $page]);
    }

    public function go(Request $request, string $code, int $i)
    {
        $page = Page::withoutTenantScope()->where('code', $code)->first();

        if (! $page || ! $page->is_active) {
            return redirect()->to(url('/p/' . $code));
        }

        $links = $page->cleanLinks();
        $link = $links[$i] ?? null;

        if (! $link || empty($link['url'])) {
            return redirect()->to(url('/p/' . $code));
        }

        $this->logClick($request, $page, $i, $link);

        return redirect()->away($link['url'], 302);
    }

    private function logClick(Request $request, Page $page, int $i, array $link): void
    {
        try {
            PageLinkClick::create([
                'tenant_id' => $page->tenant_id,
                'page_id' => $page->id,
                'link_index' => $i,
                'label' => $link['label'] ?? null,
                'url' => mb_substr($link['url'], 0, 2048),
                'occurred_at' => Carbon::now(),
                'device' => Device::detect($request->userAgent()),
                'ip_hash' => Device::hashIp($request->ip()),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
