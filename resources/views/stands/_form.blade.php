@php
    $type = old('destination_type', $stand->destination_type);
    $existingLinks = $stand->destination_type === 'hub' ? ($stand->destination_config['links'] ?? []) : [];
    $hubTitle = $stand->destination_config['title'] ?? '';
    $directUrl = $stand->destination_type === 'direct' ? ($stand->destination_config['url'] ?? '') : '';
    $surveyId = old('survey_id', $stand->destination_type === 'survey' ? ($stand->destination_config['survey_id'] ?? '') : '');
    $placeId = old('google_place_id', $stand->destination_type === 'reviews' ? ($stand->destination_config['google_place_id'] ?? '') : '');
    $surveys = $surveys ?? collect();
    $places = $places ?? collect();
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Etiqueta</label>
        <input type="text" name="label" class="form-control" maxlength="120"
               value="{{ old('label', $stand->label) }}" placeholder="ej. Mesa 4 / Entrada">
    </div>
    <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select name="status" class="form-select">
            @foreach (['active' => 'Activo', 'disabled' => 'Deshabilitado'] as $v => $l)
                <option value="{{ $v }}" @selected(old('status', $stand->status) === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Tipo</label>
        <select name="destination_type" id="destType" class="form-select">
            @foreach (\App\Models\Stand::typeOptions() as $v => $l)
                <option value="{{ $v }}" @selected($type === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
</div>

<hr class="hr-token my-4">

{{-- Direct --}}
<div data-dest="direct" class="{{ $type === 'direct' ? '' : 'd-none' }}">
    <label class="form-label">URL de destino</label>
    <input type="url" name="url" class="form-control" maxlength="2048"
           value="{{ old('url', $directUrl) }}" placeholder="https://…">
    <div class="form-text text-muted-2">La etiqueta redirige directo aquí al escanear.</div>
</div>

{{-- Survey --}}
<div data-dest="survey" class="{{ $type === 'survey' ? '' : 'd-none' }}">
    <label class="form-label">Encuesta</label>
    @if ($surveys->isEmpty())
        <div class="text-muted-2" style="font-size:.9rem">Aún no hay encuestas. <a href="{{ route('surveys.create') }}">Crea una</a> primero.</div>
    @else
        <select name="survey_id" class="form-select">
            <option value="">Elige una encuesta…</option>
            @foreach ($surveys as $s)
                <option value="{{ $s->id }}" @selected((string)$surveyId === (string)$s->id)>
                    {{ $s->title }} @if($s->status !== 'active') ({{ ucfirst($s->status) }}) @endif
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted-2">Solo las encuestas <b>activas</b> recopilan respuestas.</div>
    @endif
</div>

{{-- Google review --}}
<div data-dest="reviews" class="{{ $type === 'reviews' ? '' : 'd-none' }}">
    <label class="form-label">Lugar de Google</label>
    @if ($places->isEmpty())
        <div class="text-muted-2" style="font-size:.9rem">No hay lugares conectados. <a href="{{ route('google.index') }}">Conecta uno</a> primero.</div>
    @else
        <select name="google_place_id" class="form-select">
            <option value="">Elige un lugar…</option>
            @foreach ($places as $p)
                <option value="{{ $p->id }}" @selected((string)$placeId === (string)$p->id)>
                    {{ $p->label ?: $p->name ?: $p->place_id }}
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted-2">Los escaneos van directo a la pantalla de "escribir una reseña" de Google para este lugar.</div>
    @endif
</div>

{{-- Hub --}}
<div data-dest="hub" class="{{ $type === 'hub' ? '' : 'd-none' }}">
    <div class="mb-3">
        <label class="form-label">Título del hub</label>
        <input type="text" name="hub_title" class="form-control" maxlength="120"
               value="{{ old('hub_title', $hubTitle) }}" placeholder="Se muestra sobre los botones">
    </div>

    <label class="form-label">Enlaces</label>
    <div id="linkRows" class="d-flex flex-column gap-2">
        @php($rows = old('link_url') ? array_map(fn($u,$i)=>['label'=>old('link_label')[$i]??'','url'=>$u], old('link_url'), array_keys(old('link_url'))) : $existingLinks)
        @forelse ($rows as $link)
            <div class="d-flex gap-2 link-row">
                <input type="text" name="link_label[]" class="form-control" placeholder="Texto del botón" value="{{ $link['label'] ?? '' }}">
                <input type="url" name="link_url[]" class="form-control" placeholder="https://…" value="{{ $link['url'] ?? '' }}">
                <button type="button" class="btn btn-outline-secondary remove-row"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @empty
            <div class="d-flex gap-2 link-row">
                <input type="text" name="link_label[]" class="form-control" placeholder="Texto del botón">
                <input type="url" name="link_url[]" class="form-control" placeholder="https://…">
                <button type="button" class="btn btn-outline-secondary remove-row"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endforelse
    </div>
    <button type="button" id="addLink" class="btn btn-outline-secondary btn-sm mt-2">
        <i class="fa-solid fa-plus me-1"></i> Agregar enlace
    </button>
</div>

@push('scripts')
<script>
    (function () {
        const type = document.getElementById('destType');
        const panels = document.querySelectorAll('[data-dest]');
        function sync() { panels.forEach(p => p.classList.toggle('d-none', p.dataset.dest !== type.value)); }
        type.addEventListener('change', sync); sync();

        const rows = document.getElementById('linkRows');
        document.getElementById('addLink')?.addEventListener('click', () => {
            const row = rows.querySelector('.link-row').cloneNode(true);
            row.querySelectorAll('input').forEach(i => i.value = '');
            rows.appendChild(row);
        });
        rows?.addEventListener('click', (e) => {
            if (e.target.closest('.remove-row') && rows.querySelectorAll('.link-row').length > 1) {
                e.target.closest('.link-row').remove();
            }
        });
    })();
</script>
@endpush
