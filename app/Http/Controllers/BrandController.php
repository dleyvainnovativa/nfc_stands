<?php

namespace App\Http\Controllers;

use App\Support\CurrentTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Per-tenant branding (colours + logo) applied to that client's public
 * surveys and thank-you pages. A client-admin edits their own tenant; a
 * super-admin edits the tenant they're currently viewing ("Ver como").
 */
class BrandController extends Controller
{
    public function edit()
    {
        $tenant = CurrentTenant::model();

        if (! $tenant) {
            return redirect()->route('admin.tenants.index')
                ->with('flash.warning', 'Entra a un cliente con «Ver como» para editar su marca.');
        }

        return view('brand.edit', compact('tenant'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = CurrentTenant::model();

        if (! $tenant) {
            return redirect()->route('admin.tenants.index')
                ->with('flash.warning', 'Entra a un cliente con «Ver como» para editar su marca.');
        }

        $data = $request->validate([
            'primary_color'   => ['nullable', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'secondary_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{6})$/'],
            // SVG is intentionally excluded — it can carry scripts and renders on public pages.
            'logo'            => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ], [], [
            'primary_color'   => 'color primario',
            'secondary_color' => 'color secundario',
            'logo'            => 'logotipo',
        ]);

        if ($request->boolean('remove_logo') && $tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
            $tenant->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $tenant->logo_path = $request->file('logo')->store('brand-logos', 'public');
        }

        $tenant->primary_color = $data['primary_color'] ?? null;
        $tenant->secondary_color = $data['secondary_color'] ?? null;
        $tenant->save();

        return redirect()->route('brand.edit')->with('flash.success', 'Marca actualizada.');
    }
}
