@extends('layouts.app')
@section('title', 'Reseñas de Google · NFC Stands')
@section('page-title', 'Reseñas de Google')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1">Reseñas de Google</h1>
        <p class="text-muted-2 mb-0" style="font-size:.9rem">Tendencias de calificación y reseñas por lugar conectado.</p>
    </div>
    @if ($places->isNotEmpty())
        <form method="POST" action="{{ route('google.sync') }}">
            @csrf
            <button class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-rotate me-1"></i> Sincronizar todo</button>
        </form>
    @endif
</div>

@unless ($keyConfigured)
    <div class="card mb-4" style="border-left:3px solid var(--warning)">
        <div class="card-body d-flex align-items-center gap-2" style="font-size:.9rem">
            <i class="fa-solid fa-triangle-exclamation" style="color:var(--warning)"></i>
            <span><b>GOOGLE_PLACES_API_KEY</b> no está configurada. Agrégala a <code>.env</code> (con Places API New habilitada) para sincronizar.</span>
        </div>
    </div>
@endunless

{{-- Connect a place --}}
<div class="card mb-4" style="max-width:720px">
    <div class="card-body">
        <h2 class="h6 fw-bold mb-3">Conectar un lugar</h2>
        <form method="POST" action="{{ route('google.store') }}" class="row g-2">
            @csrf
            <div class="col-md-5">
                <input type="text" name="place_id" value="{{ old('place_id') }}" class="form-control"
                       placeholder="ID de lugar de Google (ChIJ…)" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="label" value="{{ old('label') }}" class="form-control" placeholder="Etiqueta (opcional)">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100">Conectar</button>
            </div>
        </form>
        <div class="form-text text-muted-2 mt-2">
            Encuentra un ID de lugar con el
            <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener">buscador de Place ID</a> de Google.
        </div>
    </div>
</div>

@forelse ($places as $place)
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <div class="fw-semibold">{{ $place->displayName() }}</div>
                    <div class="text-subtle mono" style="font-size:.78rem">{{ $place->place_id }}</div>
                    <div class="text-subtle" style="font-size:.78rem">
                        Última sincronización: {{ $place->last_synced_at?->diffForHumans() ?? 'nunca' }}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('google.sync.one', $place) }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-rotate"></i></button>
                    </form>
                    <form method="POST" action="{{ route('google.destroy', $place) }}"
                          onsubmit="return confirm('¿Eliminar este lugar y su historial?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-secondary btn-sm" style="color:var(--danger)"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>

            @if ($place->sync_error)
                <div class="mb-3" style="color:var(--danger);font-size:.85rem">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $place->sync_error }}
                </div>
            @endif

            <div class="row g-3">
                <div class="col-sm-3">
                    <div class="stat-label">Calificación</div>
                    <div class="stat-value">{{ $place->rating !== null ? number_format($place->rating, 1) : '—' }}
                        <i class="fa-solid fa-star" style="color:var(--brand-gold);font-size:1rem"></i>
                    </div>
                    <div class="text-muted-2" style="font-size:.82rem">{{ $place->user_rating_count !== null ? number_format($place->user_rating_count) . ' reseñas' : '' }}</div>
                </div>
                <div class="col-sm-9">
                    <div class="stat-label mb-1">Calificación · últimos 30 días</div>
                    <canvas class="ratingChart" height="70"
                            data-labels='@json($trends[$place->id]["labels"])'
                            data-ratings='@json($trends[$place->id]["ratings"])'></canvas>
                </div>
            </div>

            @if (!empty($place->reviews))
                <hr class="hr-token my-3">
                <div class="stat-label mb-2">Reseñas recientes</div>
                <div class="d-flex flex-column gap-2">
                    @foreach ($place->reviews as $rv)
                        <div class="p-2 rounded" style="background:var(--bg-sunken)">
                            <div class="d-flex justify-content-between" style="font-size:.85rem">
                                <span class="fw-semibold">{{ $rv['author'] ?? 'Anónimo' }}</span>
                                <span class="text-muted-2">
                                    {{ $rv['rating'] ?? '—' }} ★ · {{ $rv['relative_time'] ?? '' }}
                                </span>
                            </div>
                            @if (!empty($rv['text']))
                                <div class="text-muted-2 mt-1" style="font-size:.88rem">{{ \Illuminate\Support\Str::limit($rv['text'], 280) }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@empty
    @if ($keyConfigured)
        <div class="card"><div class="card-body text-center text-muted-2 py-4">
            Aún no hay lugares conectados. Agrega un ID de lugar arriba para empezar a seguir su calificación.
        </div></div>
    @endif
@endforelse

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.3/chart.umd.min.js"></script>
<script>
(function () {
    if (!window.Chart) return;
    const css = getComputedStyle(document.documentElement);
    const gold = css.getPropertyValue('--brand-gold').trim() || '#d8a657';
    const muted = css.getPropertyValue('--text-muted').trim() || '#667085';
    const grid = css.getPropertyValue('--border').trim() || '#e4e7ec';

    document.querySelectorAll('.ratingChart').forEach((el) => {
        const labels = JSON.parse(el.dataset.labels || '[]')
            .map(d => { const x = new Date(d); return (x.getMonth()+1) + '/' + x.getDate(); });
        const ratings = JSON.parse(el.dataset.ratings || '[]');
        new Chart(el, {
            type: 'line',
            data: { labels, datasets: [{
                data: ratings, borderColor: gold, backgroundColor: gold + '22',
                fill: true, tension: 0.3, pointRadius: 0, borderWidth: 2, spanGaps: true,
            }]},
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: muted, maxTicksLimit: 8 } },
                    y: { min: 0, max: 5, grid: { color: grid }, ticks: { color: muted, stepSize: 1 } }
                }
            }
        });
    });
})();
</script>
@endpush
@endsection
