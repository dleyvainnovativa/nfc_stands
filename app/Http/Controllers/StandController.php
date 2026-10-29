<?php

namespace App\Http\Controllers;

use App\Models\GooglePlace;
use App\Models\Page;
use App\Models\Stand;
use App\Models\Survey;
use App\Support\CurrentTenant;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StandController extends Controller
{
    public function index(Request $request)
    {
        $stands = Stand::query()
            ->when($request->filled('q'), fn ($q) =>
                $q->where(fn ($w) =>
                    $w->where('label', 'like', '%' . $request->string('q') . '%')
                      ->orWhere('code', 'like', '%' . $request->string('q') . '%')
                )
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->string('status'))
            )
            ->orderByDesc('last_scan_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('stands.index', compact('stands'));
    }

    public function create()
    {
        $this->guardTenantContext();
        $stand = new Stand(['status' => Stand::STATUS_ACTIVE, 'destination_type' => Stand::TYPE_DIRECT]);
        return view('stands.create', $this->formData($stand));
    }

    public function store(Request $request)
    {
        $this->guardTenantContext();
        $data = $this->validatePayload($request);

        $stand = new Stand();
        $this->fill($stand, $data);
        if ($stand->status === Stand::STATUS_ACTIVE) {
            $stand->claimed_at = $stand->claimed_at ?? Carbon::now();
        }
        $stand->save();

        return redirect()->route('stands.show', $stand)->with('flash.success', 'Stand creado.');
    }

    public function show(Stand $stand)
    {
        $chart = $this->dailySeries($stand, 30);
        $recent = $stand->scanEvents()->orderByDesc('occurred_at')->limit(15)->get();

        return view('stands.show', compact('stand', 'chart', 'recent'));
    }

    public function edit(Stand $stand)
    {
        return view('stands.edit', $this->formData($stand));
    }

    public function update(Request $request, Stand $stand)
    {
        $data = $this->validatePayload($request);
        $this->fill($stand, $data);
        if ($stand->status === Stand::STATUS_ACTIVE && ! $stand->claimed_at) {
            $stand->claimed_at = Carbon::now();
        }
        $stand->save();

        return redirect()->route('stands.show', $stand)->with('flash.success', 'Stand actualizado.');
    }

    public function destroy(Stand $stand)
    {
        $stand->delete();
        return redirect()->route('stands.index')->with('flash.success', 'Stand eliminado.');
    }

    /* ----------------------------------------------------------------- */

    private function formData(Stand $stand): array
    {
        return [
            'stand' => $stand,
            'surveys' => CurrentTenant::id() ? Survey::orderBy('title')->get(['id', 'title', 'status', 'code']) : collect(),
            'places' => CurrentTenant::id() ? GooglePlace::orderBy('label')->get(['id', 'label', 'name', 'place_id']) : collect(),
            'pages' => CurrentTenant::id() ? Page::orderBy('name')->get(['id', 'name', 'code', 'is_active']) : collect(),
        ];
    }

    private function fill(Stand $stand, array $data): void
    {
        $stand->label = $data['label'];
        $stand->status = $data['status'];
        $stand->destination_type = $data['destination_type'];
        $stand->destination_config = $data['destination_config'];
    }

    private function validatePayload(Request $request): array
    {
        $base = $request->validate([
            'label' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in([
                Stand::STATUS_ACTIVE, Stand::STATUS_DISABLED, Stand::STATUS_UNASSIGNED,
            ])],
            'destination_type' => ['required', Rule::in(array_keys(Stand::typeOptions()))],
        ]);

        switch ($base['destination_type']) {
            case Stand::TYPE_DIRECT:
                $extra = $request->validate(['url' => ['required', 'url', 'max:2048']]);
                $config = ['url' => $extra['url']];
                break;

            case Stand::TYPE_SURVEY:
                $extra = $request->validate([
                    'survey_id' => ['required', Rule::exists('surveys', 'id')->where(fn ($q) =>
                        $q->where('tenant_id', CurrentTenant::id()))],
                ]);
                $survey = Survey::findOrFail($extra['survey_id']);
                $config = ['survey_id' => $survey->id, 'survey_code' => $survey->code];
                break;

            case Stand::TYPE_REVIEWS:
                $extra = $request->validate([
                    'google_place_id' => ['required', Rule::exists('google_places', 'id')->where(fn ($q) =>
                        $q->where('tenant_id', CurrentTenant::id()))],
                ]);
                $place = GooglePlace::findOrFail($extra['google_place_id']);
                $config = ['google_place_id' => $place->id, 'review_url' => $place->writeReviewUrl()];
                break;

            case Stand::TYPE_PAGE:
                $extra = $request->validate([
                    'page_id' => ['required', Rule::exists('pages', 'id')->where(fn ($q) =>
                        $q->where('tenant_id', CurrentTenant::id()))],
                ]);
                $page = Page::findOrFail($extra['page_id']);
                $config = ['page_id' => $page->id, 'page_code' => $page->code];
                break;

            case Stand::TYPE_WIFI:
                $extra = $request->validate([
                    'wifi_ssid' => ['required', 'string', 'max:32'],
                    'wifi_encryption' => ['required', Rule::in(['WPA2', 'WPA_WPA2', 'OPEN'])],
                    'wifi_password' => ['nullable', 'string', 'max:63', 'required_unless:wifi_encryption,OPEN'],
                ]);
                $config = [
                    'ssid' => $extra['wifi_ssid'],
                    'auth' => $extra['wifi_encryption'],
                    'password' => $extra['wifi_encryption'] === 'OPEN' ? '' : ($extra['wifi_password'] ?? ''),
                    'hidden' => $request->boolean('wifi_hidden'),
                ];
                break;

            default:
                $config = [];
        }

        $base['destination_config'] = $config;
        return $base;
    }

    private function dailySeries(Stand $stand, int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $rows = $stand->dailyStats()
            ->where('stat_date', '>=', $start->toDateString())
            ->pluck('scan_count', 'stat_date');

        $labels = []; $data = [];
        for ($d = 0; $d < $days; $d++) {
            $date = $start->copy()->addDays($d)->toDateString();
            $labels[] = $date;
            $data[] = (int) ($rows[$date] ?? 0);
        }
        return ['labels' => $labels, 'data' => $data];
    }

    private function guardTenantContext(): void
    {
        if (CurrentTenant::id() === null) {
            throw new HttpResponseException(
                redirect()->route('stands.index')->with(
                    'flash.warning',
                    'Primero selecciona un cliente (usa "Ver como" en un cliente), o genera etiquetas en blanco desde la herramienta de Etiquetas.'
                )
            );
        }
    }
}
