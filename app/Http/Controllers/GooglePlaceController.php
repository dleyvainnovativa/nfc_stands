<?php

namespace App\Http\Controllers;

use App\Models\GooglePlace;
use App\Services\GooglePlaces;
use App\Support\CurrentTenant;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class GooglePlaceController extends Controller
{
    public function index()
    {
        $places = GooglePlace::orderBy('label')->orderBy('id')->get();

        $trends = [];
        foreach ($places as $p) {
            $trends[$p->id] = $p->trend(30);
        }

        $keyConfigured = ! empty(config('googleplaces.key'));

        return view('google.index', compact('places', 'trends', 'keyConfigured'));
    }

    public function store(Request $request, GooglePlaces $service)
    {
        $this->guardTenantContext();

        $data = $request->validate([
            'place_id' => ['required', 'string', 'min:5', 'max:300'],
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        $place = GooglePlace::firstOrNew([
            'tenant_id' => CurrentTenant::id(),
            'place_id' => trim($data['place_id']),
        ]);

        if ($place->exists) {
            return back()->with('flash.warning', 'Ese lugar ya está conectado.');
        }

        $place->label = $data['label'] ?? null;
        $place->save(); // tenant_id auto-fills

        // Immediate first sync so the card isn't empty.
        $ok = $service->sync($place);

        return redirect()->route('google.index')->with(
            $ok ? 'flash.success' : 'flash.warning',
            $ok ? 'Lugar conectado y sincronizado.' : 'Lugar agregado, pero la primera sincronización falló: revisa la API key / ID del lugar y vuelve a sincronizar.'
        );
    }

    public function sync(Request $request, GooglePlaces $service, ?GooglePlace $place = null)
    {
        if ($place) {
            $ok = $service->sync($place);
            return back()->with($ok ? 'flash.success' : 'flash.error',
                $ok ? 'Sincronizado.' : ('Sincronización fallida: ' . $place->sync_error));
        }

        // Sync all of the tenant's places.
        $places = GooglePlace::all();
        $fail = 0;
        foreach ($places as $p) {
            if (! $service->sync($p)) $fail++;
        }
        return back()->with($fail ? 'flash.warning' : 'flash.success',
            $fail ? "Sincronizado con {$fail} fallo(s)." : 'Todos los lugares sincronizados.');
    }

    public function destroy(GooglePlace $place)
    {
        $place->delete();
        return redirect()->route('google.index')->with('flash.success', 'Lugar eliminado.');
    }

    private function guardTenantContext(): void
    {
        if (CurrentTenant::id() === null) {
            throw new HttpResponseException(
                redirect()->route('google.index')->with(
                    'flash.warning',
                    'Primero selecciona un cliente (usa "Ver como" en un cliente) para conectar un lugar.'
                )
            );
        }
    }
}
