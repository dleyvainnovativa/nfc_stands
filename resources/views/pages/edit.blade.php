@extends('layouts.app')
@section('title', 'Editar página · NFC Stands')
@section('page-title', 'Editar página')

@php
    use App\Models\Page;
    $primary = old('primary_color', $page->primary_color ?: Page::DEFAULT_PRIMARY);
    $secondary = old('secondary_color', $page->secondary_color ?: Page::DEFAULT_SECONDARY);
    $bgColor = old('bg_color', $page->bg_color ?: Page::DEFAULT_BG);
    if (old('link_url')) {
        $rows = [];
        foreach (old('link_url') as $i => $u) {
            $rows[] = ['label' => old('link_label')[$i] ?? '', 'url' => $u, 'icon' => old('link_icon')[$i] ?? ''];
        }
    } else {
        $rows = $page->cleanLinks();
    }
@endphp

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <a href="{{ route('pages.index') }}" class="text-muted-2" style="font-size:.9rem">
        <i class="fa-solid fa-arrow-left me-1"></i> Páginas
    </a>
    <div class="d-flex align-items-center gap-2" style="min-width:0">
        @php($cls = $page->is_active ? 'success' : 'warning')
        <span class="badge-role" style="background:var(--{{ $cls }}-soft);color:var(--{{ $cls }})">{{ $page->statusLabel() }}</span>
        <code class="text-muted-2 text-truncate d-inline-block" style="font-size:.82rem;max-width:min(40vw,240px)" title="{{ $page->publicUrl() }}">{{ $page->publicUrl() }}</code>
        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" id="copyPageUrl" data-url="{{ $page->publicUrl() }}" title="Copiar enlace"><i class="fa-regular fa-copy"></i></button>
        <a href="{{ route('pages.qr', $page) }}?dl=1" class="btn btn-outline-secondary btn-sm flex-shrink-0" title="Descargar QR"><i class="fa-solid fa-qrcode"></i></a>
        <a href="{{ route('page.public', $page->code) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm flex-shrink-0">Ver</a>
    </div>
</div>

@if (auth()->user()->isSuperAdmin())
    <div class="d-flex gap-2 mb-3">
        <form method="POST" action="{{ route('pages.toggle', $page) }}">@csrf
            <button class="btn btn-sm {{ $page->is_active ? 'btn-outline-secondary' : 'btn-primary' }}">
                {{ $page->is_active ? 'Ocultar' : 'Publicar' }}
            </button>
        </form>
        <form method="POST" action="{{ route('pages.destroy', $page) }}" onsubmit="return confirm('¿Eliminar esta página?')">@csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-secondary" style="color:var(--danger)"><i class="fa-solid fa-trash me-1"></i> Eliminar</button>
        </form>
    </div>
@endif

<div class="row g-4">
    {{-- Editor --}}
    <div class="col-lg-7">
        <form method="POST" action="{{ route('pages.update', $page) }}" enctype="multipart/form-data" id="pageForm">
            @csrf @method('PUT')

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" id="f_name" value="{{ old('name', $page->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" maxlength="160" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Título visible <span class="text-subtle">(opcional)</span></label>
                            <input type="text" name="title" id="f_title" value="{{ old('title', $page->title) }}" class="form-control" maxlength="160" placeholder="Igual que el nombre">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción <span class="text-subtle">(opcional)</span></label>
                            <textarea name="description" id="f_desc" class="form-control" rows="2" maxlength="2000">{{ old('description', $page->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Style --}}
            <div class="card mb-3">
                <div class="card-header">Estilo</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Color primario</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="primaryPicker" value="{{ $primary }}" style="width:44px;height:38px;padding:2px;border:1px solid var(--border-strong);border-radius:var(--radius);background:var(--bg-elevated)">
                                <input type="text" name="primary_color" id="primaryText" value="{{ $primary }}" class="form-control mono @error('primary_color') is-invalid @enderror" maxlength="7">
                            </div>
                            @error('primary_color') <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Color secundario</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="secondaryPicker" value="{{ $secondary }}" style="width:44px;height:38px;padding:2px;border:1px solid var(--border-strong);border-radius:var(--radius);background:var(--bg-elevated)">
                                <input type="text" name="secondary_color" id="secondaryText" value="{{ $secondary }}" class="form-control mono @error('secondary_color') is-invalid @enderror" maxlength="7">
                            </div>
                            @error('secondary_color') <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Color de fondo <span class="text-subtle">(modo sólido)</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="bgColorPicker" value="{{ $bgColor }}" style="width:44px;height:38px;padding:2px;border:1px solid var(--border-strong);border-radius:var(--radius);background:var(--bg-elevated)">
                                <input type="text" name="bg_color" id="bgColorText" value="{{ $bgColor }}" class="form-control mono @error('bg_color') is-invalid @enderror" maxlength="7">
                            </div>
                            @error('bg_color') <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Color de texto</label>
                            <select name="text_mode" id="f_textmode" class="form-select">
                                @foreach (Page::textModeOptions() as $v => $l)
                                    <option value="{{ $v }}" @selected(old('text_mode', $page->text_mode) === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Fondo</label>
                            <select name="bg_style" id="f_bg" class="form-select">
                                @foreach (Page::bgStyleOptions() as $v => $l)
                                    <option value="{{ $v }}" @selected(old('bg_style', $page->bg_style) === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Forma de botón</label>
                            <select name="button_shape" id="f_shape" class="form-select">
                                @foreach (Page::buttonShapeOptions() as $v => $l)
                                    <option value="{{ $v }}" @selected(old('button_shape', $page->button_shape) === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Relleno de botón</label>
                            <select name="button_fill" id="f_fill" class="form-select">
                                @foreach (Page::buttonFillOptions() as $v => $l)
                                    <option value="{{ $v }}" @selected(old('button_fill', $page->button_fill) === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="hr-token my-4">

                    <label class="form-label">Logotipo</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div id="logoBox" style="width:64px;height:64px;border-radius:var(--radius);border:1px solid var(--border);display:grid;place-items:center;background:var(--bg-sunken);overflow:hidden">
                            @if ($page->logoUrl())
                                <img src="{{ $page->logoUrl() }}" alt="Logo" style="max-width:100%;max-height:100%;object-fit:contain">
                            @else
                                <i class="fa-solid fa-image text-subtle"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/webp" class="form-control @error('logo') is-invalid @enderror">
                            <div class="form-text text-muted-2">PNG, JPG o WEBP · máx. 1 MB.</div>
                            @error('logo') <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @if ($page->logoUrl())
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remove_logo" name="remove_logo" value="1">
                            <label class="form-check-label text-muted-2" for="remove_logo" style="font-size:.88rem">Quitar el logotipo actual</label>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Links --}}
            <div class="card mb-3">
                <div class="card-header">Enlaces</div>
                <div class="card-body">
                    <div id="linkRows" class="d-flex flex-column gap-2">
                        @forelse ($rows as $link)
                            @include('pages._link_row', ['link' => $link])
                        @empty
                            @include('pages._link_row', ['link' => ['label' => '', 'url' => '', 'icon' => '']])
                        @endforelse
                    </div>
                    <button type="button" id="addLink" class="btn btn-outline-secondary btn-sm mt-2">
                        <i class="fa-solid fa-plus me-1"></i> Agregar enlace
                    </button>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Guardar página</button>
                <a href="{{ route('pages.index') }}" class="btn btn-outline-secondary">Listo</a>
            </div>
        </form>
    </div>

    {{-- Preview + stats --}}
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header">Vista previa</div>
            <div class="card-body">
                <div id="pagePreview" data-logo="{{ $page->logoUrl() ?? '' }}">
                    <div class="pp-wrap pp-bg-{{ $page->bg_style }}" style="--pp:{{ $primary }};--ps:{{ $secondary }};--bgc:{{ $bgColor }};--txt:{{ $page->textColor() }};--onp:{{ $page->onPrimary() }}">
                        <div class="pp-logo"></div>
                        <div class="pp-title"></div>
                        <p class="pp-desc"></p>
                        <div class="pp-links"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Estadísticas</span>
                <span class="text-muted-2" style="font-size:.85rem">{{ number_format($page->views) }} vistas · {{ number_format($totalClicks) }} clics</span>
            </div>
            <div class="card-body">
                @if ($clicks->isEmpty())
                    <div class="text-muted-2" style="font-size:.9rem">Aún no hay clics en enlaces.</div>
                @else
                    @php($links = $page->cleanLinks())
                    <div class="d-flex flex-column gap-2">
                        @foreach ($clicks as $c)
                            @php($pct = $totalClicks > 0 ? round($c->clicks / $totalClicks * 100) : 0)
                            <div>
                                <div class="d-flex justify-content-between" style="font-size:.88rem">
                                    <span class="text-truncate" style="max-width:70%">{{ $c->label ?: ($links[$c->link_index]['label'] ?? ('Enlace #' . ($c->link_index + 1))) }}</span>
                                    <span class="text-muted-2">{{ number_format($c->clicks) }} · {{ $pct }}%</span>
                                </div>
                                <div style="height:8px;border-radius:999px;background:var(--bg-sunken);overflow:hidden;margin-top:3px">
                                    <div style="height:100%;width:{{ $pct }}%;background:var(--accent);border-radius:999px"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    #pagePreview { border-radius:var(--radius); overflow:hidden; border:1px solid var(--border); }
    .pp-wrap { min-height:300px; display:flex; flex-direction:column; align-items:center; padding:1.5rem 1rem; gap:.6rem; color:var(--txt); }
    .pp-wrap.pp-bg-gradient { background:linear-gradient(160deg, var(--pp), var(--ps)); }
    .pp-wrap.pp-bg-plain { background:var(--bgc); }
    .pp-logo img { max-height:48px; max-width:150px; object-fit:contain; }
    .pp-title { font-weight:700; font-size:1.15rem; text-align:center; }
    .pp-desc { font-size:.82rem; opacity:.9; text-align:center; margin:0; }
    .pp-links { width:100%; max-width:280px; display:flex; flex-direction:column; gap:.5rem; margin-top:.3rem; }
    .pp-link { display:flex; align-items:center; justify-content:center; gap:.45rem; padding:.55rem .7rem; font-weight:600; font-size:.85rem; }
    .pp-link.shape-rounded { border-radius:10px; } .pp-link.shape-pill { border-radius:999px; } .pp-link.shape-square { border-radius:4px; }
    .pp-link.fill-solid { background:var(--pp); color:var(--onp); box-shadow:0 2px 6px rgba(0,0,0,.12); }
    .pp-link.fill-outline { background:transparent; border:2px solid var(--txt); color:var(--txt); }
</style>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('pageForm');
    const preview = document.getElementById('pagePreview');
    const wrap = preview.querySelector('.pp-wrap');
    const hex = /^#([0-9a-fA-F]{6})$/;
    let logoOverride = null;

    const el = (id) => document.getElementById(id);
    const pPick = el('primaryPicker'), pText = el('primaryText');
    const sPick = el('secondaryPicker'), sText = el('secondaryText');
    const bPick = el('bgColorPicker'), bText = el('bgColorText');

    function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
    function yiq(h) { h = (h || '').replace('#', ''); if (h.length !== 6) return 255; const r = parseInt(h.substr(0,2),16), g = parseInt(h.substr(2,2),16), b = parseInt(h.substr(4,2),16); return (r*299 + g*587 + b*114) / 1000; }
    function contrast(h) { return yiq(h) >= 150 ? '#1e2733' : '#ffffff'; }
    function blend(a, b) { a = (a || '').replace('#',''); b = (b || '').replace('#',''); if (a.length !== 6 || b.length !== 6) return '#888888'; const m = (i) => Math.round((parseInt(a.substr(i,2),16) + parseInt(b.substr(i,2),16)) / 2).toString(16).padStart(2,'0'); return '#' + m(0) + m(2) + m(4); }

    function render() {
        const prim = hex.test(pText.value) ? pText.value : '{{ $primary }}';
        const sec = hex.test(sText.value) ? sText.value : '{{ $secondary }}';
        const bgc = hex.test(bText.value) ? bText.value : '{{ $bgColor }}';
        const bgStyle = el('f_bg').value;
        const mode = el('f_textmode').value;
        const bgRef = bgStyle === 'plain' ? bgc : blend(prim, sec);
        const txt = mode === 'light' ? '#ffffff' : mode === 'dark' ? '#1e2733' : contrast(bgRef);
        const onp = contrast(prim);

        wrap.style.setProperty('--pp', prim);
        wrap.style.setProperty('--ps', sec);
        wrap.style.setProperty('--bgc', bgc);
        wrap.style.setProperty('--txt', txt);
        wrap.style.setProperty('--onp', onp);
        wrap.className = 'pp-wrap pp-bg-' + bgStyle;

        const shape = 'shape-' + el('f_shape').value;
        const fill = 'fill-' + el('f_fill').value;

        wrap.querySelector('.pp-title').textContent = el('f_title').value.trim() || el('f_name').value.trim() || 'Título';
        const desc = wrap.querySelector('.pp-desc');
        desc.textContent = el('f_desc').value.trim();
        desc.style.display = el('f_desc').value.trim() ? '' : 'none';

        const logoUrl = logoOverride || preview.dataset.logo;
        wrap.querySelector('.pp-logo').innerHTML = logoUrl ? '<img src="' + logoUrl + '" alt="logo">' : '';

        let html = '';
        document.querySelectorAll('#linkRows .link-row').forEach(row => {
            const label = row.querySelector('[name="link_label[]"]').value.trim();
            const url = row.querySelector('[name="link_url[]"]').value.trim();
            const icon = row.querySelector('[name="link_icon[]"]').value;
            const lbg = row.querySelector('.link-bg-val').value.trim();
            const ltx = row.querySelector('.link-text-val').value.trim();
            if (!label && !url) return;
            const ico = icon ? '<i class="' + icon + '"></i> ' : '';
            let cls, styleAttr = '';
            if (lbg) {
                cls = 'pp-link ' + shape;
                styleAttr = ' style="background:' + lbg + ';color:' + (ltx || contrast(lbg)) + ';border:none;box-shadow:0 2px 6px rgba(0,0,0,.12);"';
            } else {
                cls = 'pp-link ' + shape + ' ' + fill;
            }
            html += '<span class="' + cls + '"' + styleAttr + '>' + ico + esc(label || url) + '</span>';
        });
        wrap.querySelector('.pp-links').innerHTML = html;
    }

    // Colour pickers ↔ text
    pPick.addEventListener('input', () => { pText.value = pPick.value; render(); });
    sPick.addEventListener('input', () => { sText.value = sPick.value; render(); });
    bPick.addEventListener('input', () => { bText.value = bPick.value; render(); });
    pText.addEventListener('input', () => { if (hex.test(pText.value)) pPick.value = pText.value; render(); });
    sText.addEventListener('input', () => { if (hex.test(sText.value)) sPick.value = sText.value; render(); });
    bText.addEventListener('input', () => { if (hex.test(bText.value)) bPick.value = bText.value; render(); });

    ['f_name','f_title','f_desc','f_bg','f_shape','f_fill','f_textmode'].forEach(id => {
        el(id).addEventListener('input', render);
        el(id).addEventListener('change', render);
    });

    // Links add/remove
    const rows = document.getElementById('linkRows');
    document.getElementById('addLink').addEventListener('click', () => {
        const row = rows.querySelector('.link-row').cloneNode(true);
        row.querySelectorAll('input[type="text"], input[type="url"]').forEach(i => i.value = '');
        row.querySelector('select').selectedIndex = 0;
        const chk = row.querySelector('.link-custom'); if (chk) chk.checked = false;
        row.querySelector('.link-colors')?.classList.add('d-none');
        const bgv = row.querySelector('.link-bg-val'); if (bgv) bgv.value = '';
        const txv = row.querySelector('.link-text-val'); if (txv) txv.value = '';
        const bgp = row.querySelector('.link-bg-pick'); if (bgp) bgp.value = '#2e6be6';
        const txp = row.querySelector('.link-text-pick'); if (txp) txp.value = '#ffffff';
        rows.appendChild(row);
        render();
    });
    rows.addEventListener('click', (e) => {
        if (e.target.closest('.remove-row') && rows.querySelectorAll('.link-row').length > 1) {
            e.target.closest('.link-row').remove();
            render();
        }
    });
    rows.addEventListener('change', (e) => {
        if (e.target.classList.contains('link-custom')) {
            const row = e.target.closest('.link-row');
            const colors = row.querySelector('.link-colors');
            const bgVal = row.querySelector('.link-bg-val');
            const txVal = row.querySelector('.link-text-val');
            if (e.target.checked) {
                colors.classList.remove('d-none');
                bgVal.value = row.querySelector('.link-bg-pick').value;
                txVal.value = row.querySelector('.link-text-pick').value;
            } else {
                colors.classList.add('d-none');
                bgVal.value = '';
                txVal.value = '';
            }
        }
        render();
    });
    rows.addEventListener('input', (e) => {
        if (e.target.classList.contains('link-bg-pick')) {
            e.target.closest('.link-row').querySelector('.link-bg-val').value = e.target.value;
        } else if (e.target.classList.contains('link-text-pick')) {
            e.target.closest('.link-row').querySelector('.link-text-val').value = e.target.value;
        }
        render();
    });

    // Logo live preview
    const logoInput = document.getElementById('logoInput');
    logoInput.addEventListener('change', () => {
        const f = logoInput.files && logoInput.files[0];
        if (!f) return;
        logoOverride = URL.createObjectURL(f);
        document.getElementById('logoBox').innerHTML = '<img src="' + logoOverride + '" style="max-width:100%;max-height:100%;object-fit:contain">';
        render();
    });

    // Copy URL
    document.getElementById('copyPageUrl')?.addEventListener('click', async (e) => {
        try { await navigator.clipboard.writeText(e.currentTarget.dataset.url); window.App?.toast('Enlace copiado', 'success'); }
        catch (err) { window.App?.toast('No se pudo copiar', 'error'); }
    });

    render();
})();
</script>
@endpush
@endsection
