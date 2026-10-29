@php
    $type = old('destination_type', $stand->destination_type);
    $directUrl = $stand->destination_type === 'direct' ? ($stand->destination_config['url'] ?? '') : '';
    $surveyId = old('survey_id', $stand->destination_type === 'survey' ? ($stand->destination_config['survey_id'] ?? '') : '');
    $placeId = old('google_place_id', $stand->destination_type === 'reviews' ? ($stand->destination_config['google_place_id'] ?? '') : '');
    $pageId = old('page_id', $stand->destination_type === 'page' ? ($stand->destination_config['page_id'] ?? '') : '');
    $surveys = $surveys ?? collect();
    $places = $places ?? collect();
    $pages = $pages ?? collect();
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

{{-- Página (linktree) --}}
<div data-dest="page" class="{{ $type === 'page' ? '' : 'd-none' }}">
    <label class="form-label">Página</label>
    @if ($pages->isEmpty())
        <div class="text-muted-2" style="font-size:.9rem">
            Aún no hay páginas.
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('pages.create') }}">Crea una</a> primero.
            @else
                Pide al administrador que cree una.
            @endif
        </div>
    @else
        <select name="page_id" class="form-select">
            <option value="">Elige una página…</option>
            @foreach ($pages as $p)
                <option value="{{ $p->id }}" @selected((string)$pageId === (string)$p->id)>
                    {{ $p->name }} @unless($p->is_active) (oculta) @endunless
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted-2">La etiqueta redirige a la página pública. Edítala en <a href="{{ route('pages.index') }}">Páginas</a>.</div>
    @endif
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

@push('scripts')
<script>
    (function () {
        const type = document.getElementById('destType');
        const panels = document.querySelectorAll('[data-dest]');
        function sync() { panels.forEach(p => p.classList.toggle('d-none', p.dataset.dest !== type.value)); }
        type.addEventListener('change', sync); sync();
    })();
</script>
@endpush
