<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageLinkClick;
use App\Services\Qr;
use App\Support\CurrentTenant;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Linktree-style Pages. The admin creates them and controls publishing
 * (store / toggle / destroy are super-admin only, enforced on the routes).
 * Clients edit content + styling of their own tenant's pages.
 */
class PageController extends Controller
{
    public function index()
    {
        $pages = Page::withCount('linkClicks')
            ->orderByDesc('id')
            ->paginate(20);

        return view('pages.index', compact('pages'));
    }

    public function create()
    {
        $this->guardTenantContext();
        return view('pages.create', ['page' => new Page()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->guardTenantContext();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        $page = Page::create([
            'name' => $data['name'],
            'title' => $data['name'],
            'is_active' => false,
        ]);

        return redirect()->route('pages.edit', $page)
            ->with('flash.success', 'Página creada. Edita su contenido; el admin la publica.');
    }

    public function edit(Page $page)
    {
        $clicks = PageLinkClick::where('page_id', $page->id)
            ->selectRaw('link_index, MAX(label) as label, COUNT(*) as clicks')
            ->groupBy('link_index')
            ->orderByDesc('clicks')
            ->get();

        return view('pages.edit', [
            'page' => $page,
            'clicks' => $clicks,
            'totalClicks' => (int) $clicks->sum('clicks'),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'primary_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'secondary_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'button_shape' => ['required', Rule::in(array_keys(Page::buttonShapeOptions()))],
            'button_fill' => ['required', Rule::in(array_keys(Page::buttonFillOptions()))],
            'bg_style' => ['required', Rule::in(array_keys(Page::bgStyleOptions()))],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'link_label' => ['array'],
            'link_label.*' => ['nullable', 'string', 'max:80'],
            'link_url' => ['array'],
            'link_url.*' => ['nullable', 'url', 'max:2048'],
            'link_icon' => ['array'],
            'link_icon.*' => ['nullable', 'string', Rule::in(array_keys(Page::iconOptions()))],
        ]);

        // Build the link list (skip rows without a URL).
        $labels = $request->input('link_label', []);
        $urls = $request->input('link_url', []);
        $icons = $request->input('link_icon', []);
        $links = [];
        foreach ($urls as $i => $u) {
            $u = trim((string) $u);
            if ($u === '') {
                continue;
            }
            $links[] = [
                'label' => trim((string) ($labels[$i] ?? '')) ?: $u,
                'url'   => $u,
                'icon'  => ($icons[$i] ?? '') ?: null,
            ];
        }

        if ($request->boolean('remove_logo') && $page->logo_path) {
            Storage::disk('public')->delete($page->logo_path);
            $page->logo_path = null;
        }
        if ($request->hasFile('logo')) {
            if ($page->logo_path) {
                Storage::disk('public')->delete($page->logo_path);
            }
            $page->logo_path = $request->file('logo')->store('page-logos', 'public');
        }

        $page->fill([
            'name' => $data['name'],
            'title' => $data['title'] ?: $data['name'],
            'description' => $data['description'] ?? null,
            'primary_color' => $data['primary_color'] ?? null,
            'secondary_color' => $data['secondary_color'] ?? null,
            'button_shape' => $data['button_shape'],
            'button_fill' => $data['button_fill'],
            'bg_style' => $data['bg_style'],
            'links' => $links,
        ])->save();

        return redirect()->route('pages.edit', $page)
            ->with('flash.success', 'Página guardada.');
    }

    /* ---- Super-admin only (route-guarded) ---- */

    public function toggle(Page $page): RedirectResponse
    {
        $page->is_active = ! $page->is_active;
        $page->save();

        return back()->with('flash.success',
            $page->is_active ? 'Página publicada.' : 'Página ocultada.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        if ($page->logo_path) {
            Storage::disk('public')->delete($page->logo_path);
        }
        $page->delete();

        return redirect()->route('pages.index')->with('flash.success', 'Página eliminada.');
    }

    /** QR of the page's public URL. ?dl=1 forces a download. */
    public function qr(Request $request, Page $page)
    {
        $svg = Qr::svg($page->publicUrl(), 320, 2);
        $headers = ['Content-Type' => 'image/svg+xml'];
        if ($request->boolean('dl')) {
            $headers['Content-Disposition'] = 'attachment; filename="page-' . $page->code . '.svg"';
        } else {
            $headers['Cache-Control'] = 'private, max-age=600';
        }
        return response($svg, 200, $headers);
    }

    private function guardTenantContext(): void
    {
        if (CurrentTenant::id() === null) {
            throw new HttpResponseException(
                redirect()->route('pages.index')->with(
                    'flash.warning',
                    'Primero selecciona un cliente (usa "Ver como" en un cliente) para crear una página.'
                )
            );
        }
    }
}
