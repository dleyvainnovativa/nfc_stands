@extends('layouts.app')
@section('title', ($stand->label ?: $stand->code) . ' · NFC Stands')
@section('page-title', 'Stand')

@section('content')
<div class="mb-3">
    <a href="{{ route('stands.index') }}" class="text-muted-2" style="font-size:.9rem">
        <i class="fa-solid fa-arrow-left me-1"></i> Stands
    </a>
</div>

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">{{ $stand->label ?: 'Stand sin título' }}</h1>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @php($cls = match($stand->status){ 'active' => 'success', 'disabled' => 'danger', default => 'warning' })
            <span class="badge-role" style="background:var(--{{ $cls }}-soft);color:var(--{{ $cls }})">{{ $stand->statusLabel() }}</span>
            <span class="text-muted-2" style="font-size:.88rem">{{ \App\Models\Stand::typeOptions()[$stand->destination_type] ?? $stand->destination_type }}</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stands.edit', $stand) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-pen me-1"></i> Editar
        </a>
        @if(auth()->user()->isSuperAdmin())
        <form method="POST" action="{{ route('stands.destroy', $stand) }}"
              onsubmit="return confirm('¿Eliminar este stand? También se borra el historial de escaneos.')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-secondary btn-sm" style="color:var(--danger)"><i class="fa-solid fa-trash"></i></button>
        </form>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Tag URL + QR --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted-2" style="font-size:.85rem">URL de la etiqueta</span>
                <code id="pubUrl" class="flex-grow-1">{{ $stand->publicUrl() }}</code>
                <button class="btn btn-outline-secondary btn-sm" id="copyUrl" type="button">
                    <i class="fa-regular fa-copy me-1"></i> Copiar
                </button>
                <a href="{{ $stand->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Probar
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="{{ route('stands.qr', $stand) }}" alt="QR for {{ $stand->code }}"
                     width="92" height="92" style="border-radius:8px;background:#fff;padding:4px;border:1px solid var(--border)">
                <div>
                    <div class="fw-semibold" style="font-size:.92rem">Código QR</div>
                    <div class="text-muted-2 mb-2" style="font-size:.82rem">Para teléfonos sin NFC. Apunta a la misma URL de la etiqueta.</div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('stands.qr.print', $stand) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-print me-1"></i> Imprimir
                        </a>
                        <a href="{{ route('stands.qr', $stand) }}?dl=1" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-download me-1"></i> SVG
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card stat-card h-100"><div class="stat-label">Escaneos totales</div>
            <div class="stat-value">{{ number_format($stand->scans_total) }}</div></div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card stat-card h-100"><div class="stat-label">Escaneos (30d)</div>
            <div class="stat-value">{{ number_format(array_sum($chart['data'])) }}</div></div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card stat-card h-100"><div class="stat-label">Último escaneo</div>
            <div class="stat-value" style="font-size:1.15rem">{{ $stand->last_scan_at?->diffForHumans() ?? '—' }}</div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Escaneos · últimos 30 días</div>
    <div class="card-body"><canvas id="scanChart" height="90"></canvas></div>
</div>

@if ($stand->destination_type === \App\Models\Stand::TYPE_PAGE && !empty($stand->destination_config['page_id']))
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon"><i class="fa-solid fa-link"></i></div>
            <div class="flex-grow-1">
                <div class="fw-semibold">Página vinculada</div>
                <div class="text-muted-2" style="font-size:.9rem">Los clics en los enlaces se miden en la página.</div>
            </div>
            <a href="{{ route('pages.edit', $stand->destination_config['page_id']) }}" class="btn btn-outline-secondary btn-sm">Ver página</a>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">Escaneos recientes</div>
    <div class="table-responsive">
        <table class="table table-cards align-middle mb-0">
            <thead><tr class="text-muted-2" style="font-size:.8rem"><th class="ps-3">Cuándo</th><th>Dispositivo</th><th>Origen</th></tr></thead>
            <tbody>
                @forelse ($recent as $ev)
                    <tr>
                        <td class="cell-title ps-3" style="font-size:.88rem">{{ $ev->occurred_at->format('d M, H:i') }}</td>
                        <td data-label="Dispositivo"><span class="text-muted-2">{{ ucfirst($ev->device ?? '—') }}</span></td>
                        <td class="text-muted-2 text-truncate" style="max-width:260px;font-size:.85rem" data-label="Origen">{{ $ev->referer ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted-2 py-4">Aún no hay escaneos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.3/chart.umd.min.js"></script>
<script>
    (function () {
        const el = document.getElementById('scanChart');
        if (!el || !window.Chart) return;
        const css = getComputedStyle(document.documentElement);
        const accent = css.getPropertyValue('--accent').trim() || '#5b5bd6';
        const muted = css.getPropertyValue('--text-muted').trim() || '#667085';
        const grid = css.getPropertyValue('--border').trim() || '#e4e7ec';
        new Chart(el, {
            type: 'line',
            data: {
                labels: @json(array_map(fn($d) => \Illuminate\Support\Carbon::parse($d)->format('d M'), $chart['labels'])),
                datasets: [{ data: @json($chart['data']), borderColor: accent, backgroundColor: accent + '22',
                    fill: true, tension: 0.3, pointRadius: 0, borderWidth: 2 }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: muted, maxTicksLimit: 8 } },
                    y: { beginAtZero: true, grid: { color: grid }, ticks: { color: muted, precision: 0 } }
                }
            }
        });
        document.getElementById('copyUrl')?.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(document.getElementById('pubUrl').textContent.trim());
                window.App?.toast('URL de la etiqueta copiada', 'success');
            } catch (e) { window.App?.toast('No se pudo copiar', 'error'); }
        });
    })();
</script>
@endpush
@endsection
