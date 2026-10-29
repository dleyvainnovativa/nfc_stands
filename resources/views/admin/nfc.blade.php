@extends('layouts.app')
@section('title', 'Escribir NFC · NFC Stands')
@section('page-title', 'Escribir NFC')

@section('content')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">Escribir NFC</h1>
        <div class="text-muted-2" style="font-size:.9rem">
            Valida y graba las etiquetas físicas. Acerca una etiqueta al teléfono para leerla o escribirla.
        </div>
    </div>
    <span class="badge-role" style="background:var(--accent-soft);color:var(--accent-text)">
        <i class="fa-solid fa-tags me-1"></i> {{ number_format($blankCount) }} en el pool
    </span>
</div>

{{-- Unsupported browser notice (shown via JS only when Web NFC is missing) --}}
<div id="nfcUnsupported" class="card mb-4 d-none" style="border-color:var(--warning)">
    <div class="card-body d-flex gap-3 align-items-start">
        <div class="stat-icon" style="background:var(--warning-soft);color:var(--warning)">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <div class="fw-semibold mb-1">Este teléfono/navegador no soporta Web NFC</div>
            <div class="text-muted-2" style="font-size:.9rem">
                Web NFC solo funciona en <b>Chrome para Android</b> sobre <b>HTTPS</b>. Abre esta página en Chrome
                en un teléfono Android con NFC activado. (iPhone no está soportado por ahora.)
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ---- Left: queue ------------------------------------------------ --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Cola de etiquetas</span>
                <span id="queueMeta" class="text-muted-2" style="font-size:.8rem"></span>
            </div>
            <div class="card-body border-bottom border-token">
                <div class="row g-2">
                    <div class="col-12">
                        <select id="fltTenant" class="form-select form-select-sm">
                            <option value="pool">Pool · etiquetas en blanco</option>
                            <option value="">Todos los clientes</option>
                            @foreach ($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-7">
                        <input id="fltQ" type="search" class="form-control form-control-sm" placeholder="Buscar código o etiqueta…">
                    </div>
                    <div class="col-5">
                        <select id="fltEncoded" class="form-select form-select-sm">
                            <option value="0">Pendientes</option>
                            <option value="1">Escritas</option>
                            <option value="">Todas</option>
                        </select>
                    </div>
                </div>
            </div>
            <div id="queueList" class="list-scroll" style="max-height:62vh;overflow:auto">
                <div class="text-center text-muted-2 py-5"><div class="spinner-sm mx-auto mb-2"></div>Cargando…</div>
            </div>
        </div>
    </div>

    {{-- ---- Right: writer --------------------------------------------- --}}
    <div class="col-lg-7">
        {{-- Selected target --}}
        <div class="card mb-3">
            <div class="card-header">Etiqueta seleccionada</div>
            <div class="card-body">
                <div id="targetEmpty" class="text-muted-2 py-2" style="font-size:.9rem">
                    Elige una etiqueta de la cola para grabarla.
                </div>
                <div id="targetCard" class="d-none">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <code id="tgtCode" style="font-size:1rem"></code>
                        <span id="tgtType" class="badge-role" style="background:var(--accent-soft);color:var(--accent-text)"></span>
                        <span id="tgtEncoded" class="badge-role d-none" style="background:var(--success-soft);color:var(--success)">
                            <i class="fa-solid fa-check me-1"></i>Escrita
                        </span>
                    </div>
                    <div id="tgtLabel" class="fw-semibold"></div>
                    <div id="tgtTenant" class="text-muted-2 mb-3" style="font-size:.85rem"></div>

                    <div class="p-3 rounded" style="background:var(--surface-2,var(--accent-soft))">
                        <div class="text-muted-2 mb-1" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em">Se va a grabar</div>
                        <div id="tgtPayload" style="font-family:var(--font-mono,monospace);font-size:.9rem;word-break:break-all"></div>
                    </div>

                    {{-- NTAG213 byte budget --}}
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted-2" style="font-size:.8rem">Capacidad NTAG213</span>
                            <span id="budgetText" class="text-muted-2" style="font-size:.8rem"></span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div id="budgetBar" class="progress-bar" role="progressbar" style="width:0%"></div>
                        </div>
                        <div id="budgetWarn" class="form-text d-none" style="color:var(--danger)">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>No cabe en una NTAG213. Acorta el SSID/contraseña o usa una NTAG215/216.
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button id="btnWrite" class="btn btn-brand btn-sm">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Escribir en etiqueta
                        </button>
                        <button id="btnValidate" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Validar etiqueta
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live read/write status --}}
        <div class="card">
            <div class="card-header">Estado</div>
            <div class="card-body">
                <div id="nfcStatus" class="text-muted-2 py-2" style="font-size:.92rem">
                    Listo. Selecciona una etiqueta y pulsa <b>Validar</b> o <b>Escribir</b>, luego acerca la etiqueta.
                </div>
                <div id="readResult" class="d-none mt-2"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.NFC_CFG = {
    standsUrl:   @json(route('admin.nfc.stands')),
    lookupTpl:   @json(route('admin.nfc.lookup', '__CODE__')),
    encodeTpl:   @json(route('admin.nfc.encoded', '__ID__')),
    unencodeTpl: @json(route('admin.nfc.unencoded', '__ID__')),
    capacity: 144, // NTAG213 user memory (bytes)
};
</script>
<script>
(function () {
    const CFG = window.NFC_CFG;
    const $ = (id) => document.getElementById(id);
    const supported = ('NDEFReader' in window);

    let stands = [];      // current queue
    let target = null;    // selected stand
    let busy = false;     // a scan/write op is in flight

    if (!supported) $('nfcUnsupported').classList.remove('d-none');

    /* ---------- Queue ------------------------------------------------ */
    async function loadQueue() {
        const params = new URLSearchParams({
            tenant: $('fltTenant').value,
            encoded: $('fltEncoded').value,
            q: $('fltQ').value.trim(),
        });
        $('queueList').innerHTML =
            '<div class="text-center text-muted-2 py-5"><div class="spinner-sm mx-auto mb-2"></div>Cargando…</div>';
        try {
            const data = await window.App.http.get(CFG.standsUrl + '?' + params.toString());
            stands = data.stands || [];
            $('queueMeta').textContent = `${data.pending} pendientes · ${data.total} mostradas`;
            renderQueue();
        } catch (e) {
            $('queueList').innerHTML =
                '<div class="text-center text-muted-2 py-5">No se pudo cargar la cola.</div>';
        }
    }

    function renderQueue() {
        if (!stands.length) {
            $('queueList').innerHTML =
                '<div class="text-center text-muted-2 py-5">Sin etiquetas con estos filtros.</div>';
            return;
        }
        const rows = stands.map((s) => {
            const encoded = !!s.encoded_at;
            const dot = encoded
                ? '<span title="Escrita" style="color:var(--success)"><i class="fa-solid fa-circle-check"></i></span>'
                : '<span title="Pendiente" style="color:var(--warning)"><i class="fa-regular fa-circle"></i></span>';
            const active = (target && target.id === s.id) ? ' nfc-row-active' : '';
            const tenant = s.tenant ? esc(s.tenant) : '<span class="text-muted-2">Pool</span>';
            return `<button type="button" class="nfc-row${active}" data-id="${s.id}">
                <span class="nfc-row-dot">${dot}</span>
                <span class="nfc-row-main">
                    <code>${esc(s.code)}</code>
                    <span class="nfc-row-sub">${s.label ? esc(s.label) : '<span class="text-muted-2">sin etiqueta</span>'} · ${tenant}</span>
                </span>
                <span class="nfc-row-type text-muted-2">${esc(s.type_label)}</span>
            </button>`;
        }).join('');
        $('queueList').innerHTML = rows;
        $('queueList').querySelectorAll('.nfc-row').forEach((btn) => {
            btn.addEventListener('click', () => selectById(+btn.dataset.id));
        });
    }

    function selectById(id) {
        const s = stands.find((x) => x.id === id);
        if (s) setTarget(s);
    }

    /* ---------- Target ----------------------------------------------- */
    function setTarget(s) {
        target = s;
        $('targetEmpty').classList.add('d-none');
        $('targetCard').classList.remove('d-none');
        $('tgtCode').textContent = s.code;
        $('tgtType').textContent = s.type_label;
        $('tgtLabel').textContent = s.label || 'Sin etiqueta';
        $('tgtTenant').textContent = s.tenant ? s.tenant : 'Pool · sin cliente';
        $('tgtEncoded').classList.toggle('d-none', !s.encoded_at);

        // What gets written
        const p = s.payload;
        $('tgtPayload').textContent = (p.kind === 'wifi')
            ? `WiFi · ${p.ssid || '(sin SSID)'} · ${p.auth}${p.password ? ' · ' + p.password : ''}`
            : p.url;

        // Byte budget
        const bytes = estimateBytes(p);
        const pct = Math.min(100, Math.round(bytes / CFG.capacity * 100));
        const over = bytes > CFG.capacity;
        $('budgetText').textContent = `${bytes} / ${CFG.capacity} B`;
        const bar = $('budgetBar');
        bar.style.width = pct + '%';
        bar.style.background = over ? 'var(--danger)' : (pct > 85 ? 'var(--warning)' : 'var(--accent)');
        $('budgetWarn').classList.toggle('d-none', !over);
        $('btnWrite').disabled = over;

        renderQueue(); // refresh active highlight
    }

    /* ---------- NFC read (validate) ---------------------------------- */
    async function validate() {
        if (!guard()) return;
        const ctrl = new AbortController();
        setStatus('<i class="fa-solid fa-wifi fa-beat me-1"></i> Acerca la etiqueta para leerla…');
        $('readResult').classList.add('d-none');
        try {
            const reader = new NDEFReader();
            await reader.scan({ signal: ctrl.signal });
            reader.onreadingerror = () => setStatus('No se pudo leer la etiqueta. Intenta de nuevo.', true);
            reader.onreading = (event) => { ctrl.abort(); handleReading(event); };
        } catch (e) {
            fail(e);
        } finally {
            busy = false;
        }
    }

    function handleReading(event) {
        const recs = event.message.records || [];
        const serial = event.serialNumber ? `<div class="text-muted-2 mt-2" style="font-size:.78rem">UID: ${esc(event.serialNumber)}</div>` : '';

        if (!recs.length || (recs.length === 1 && recs[0].recordType === 'empty')) {
            setStatus('Etiqueta <b>vacía</b>. Lista para escribir.');
            return showResult('empty', '<div class="fw-semibold">Etiqueta vacía</div><div class="text-muted-2" style="font-size:.88rem">Pulsa “Escribir en etiqueta” para grabar la seleccionada.</div>' + serial);
        }

        const r = recs[0];
        if (r.recordType === 'url' || r.recordType === 'absolute-url') {
            const url = new TextDecoder().decode(r.data);
            const m = url.match(/\/t\/([0-9A-Za-z]{4,32})\b/);
            if (m) return showOurs(m[1], url, serial);
            return showForeign(`Enlace externo`, esc(url), serial);
        }
        if (r.recordType === 'mime' && r.mediaType === 'application/vnd.wfa.wsc') {
            return showForeign('Etiqueta WiFi', 'Contiene credenciales WiFi.', serial);
        }
        return showForeign('Contenido no reconocido', esc(r.recordType || 'desconocido'), serial);
    }

    async function showOurs(code, url, serial) {
        setStatus('Etiqueta leída.');
        try {
            const data = await window.App.http.get(CFG.lookupTpl.replace('__CODE__', encodeURIComponent(code)));
            if (!data.found) {
                return showForeign('Código desconocido', `<code>${esc(code)}</code> no existe en el servidor.`, serial);
            }
            const s = data.stand;
            const enc = s.encoded_at
                ? '<span class="badge-role" style="background:var(--success-soft);color:var(--success)">Marcada como escrita</span>'
                : '<span class="badge-role" style="background:var(--warning-soft);color:var(--warning)">No marcada como escrita</span>';
            const mismatch = (target && target.code !== s.code)
                ? `<div class="form-text" style="color:var(--warning)">Ojo: la seleccionada es <code>${esc(target.code)}</code>.</div>` : '';
            showResult('ours', `
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <code>${esc(s.code)}</code>
                    <span class="badge-role" style="background:var(--accent-soft);color:var(--accent-text)">${esc(s.type_label)}</span>
                    ${enc}
                </div>
                <div class="fw-semibold">${s.label ? esc(s.label) : 'Sin etiqueta'}</div>
                <div class="text-muted-2" style="font-size:.85rem">${s.tenant ? esc(s.tenant) : 'Pool · sin cliente'}</div>
                ${mismatch}
                <div class="d-flex gap-2 mt-3 flex-wrap">
                    ${s.encoded_at ? '' : `<button class="btn btn-outline-secondary btn-sm" data-act="mark" data-id="${s.id}" data-kind="${s.payload.kind}">Marcar como escrita</button>`}
                    <button class="btn btn-outline-secondary btn-sm" data-act="select" data-id="${s.id}">Seleccionar esta</button>
                </div>
                ${serial}
            `);
            // ensure this stand is available to select even if not in current queue
            if (!stands.find((x) => x.id === s.id)) stands.push(s);
            wireResultActions();
        } catch (e) {
            showForeign('Error de servidor', 'No se pudo validar el código.', serial);
        }
    }

    function showForeign(title, body, serial) {
        setStatus('Etiqueta leída.');
        showResult('foreign', `
            <div class="fw-semibold">${title}</div>
            <div class="text-muted-2" style="font-size:.88rem;word-break:break-all">${body}</div>
            <div class="form-text text-muted-2">Puedes sobrescribirla con la etiqueta seleccionada.</div>
            ${serial || ''}
        `);
    }

    function showResult(kind, html) {
        const box = $('readResult');
        box.className = 'mt-2 p-3 rounded nfc-result nfc-result-' + kind;
        box.innerHTML = html;
        box.classList.remove('d-none');
    }

    function wireResultActions() {
        $('readResult').querySelectorAll('[data-act]').forEach((b) => {
            b.addEventListener('click', async () => {
                const id = +b.dataset.id;
                if (b.dataset.act === 'select') return selectById(id);
                if (b.dataset.act === 'mark') {
                    await markEncoded(id, b.dataset.kind);
                    loadQueue();
                    $('readResult').classList.add('d-none');
                }
            });
        });
    }

    /* ---------- NFC write -------------------------------------------- */
    async function write() {
        if (!guard()) return;
        if (!target) return window.App.toast('Primero selecciona una etiqueta.', 'warning');

        const p = target.payload;
        const bytes = estimateBytes(p);
        if (bytes > CFG.capacity) return window.App.toast('No cabe en una NTAG213.', 'error');

        let message;
        try {
            message = (p.kind === 'wifi')
                ? { records: [{ recordType: 'mime', mediaType: 'application/vnd.wfa.wsc', data: buildWsc(p) }] }
                : { records: [{ recordType: 'url', data: p.url }] };
        } catch (e) {
            busy = false;
            return window.App.toast('Datos WiFi inválidos.', 'error');
        }

        setStatus(`<i class="fa-solid fa-wifi fa-beat me-1"></i> Acerca la etiqueta para grabar <code>${esc(target.code)}</code>…`);
        try {
            const reader = new NDEFReader();
            await reader.write(message, { overwrite: true });
            setStatus(`<span style="color:var(--success)"><i class="fa-solid fa-circle-check me-1"></i> Grabada <code>${esc(target.code)}</code>.</span>`);
            window.App.toast('Etiqueta grabada', 'success');
            await markEncoded(target.id, p.kind);
            advanceToNextPending();
            loadQueue();
        } catch (e) {
            fail(e);
        } finally {
            busy = false;
        }
    }

    function advanceToNextPending() {
        const next = stands.find((s) => !s.encoded_at && s.id !== target.id);
        if (next) setTarget(next);
    }

    async function markEncoded(id, kind) {
        try {
            await window.App.http.post(CFG.encodeTpl.replace('__ID__', id), { kind });
        } catch (e) {
            window.App.toast('Grabada, pero no se pudo sincronizar con el servidor.', 'warning');
        }
    }

    /* ---------- WiFi WSC payload (application/vnd.wfa.wsc) ------------ */
    // TLV attributes wrapped in a Credential (0x100E). Android reads this and
    // offers to join the network.
    function buildWsc(p) {
        const AUTH = { WPA2: 0x0020, WPA_WPA2: 0x0022, OPEN: 0x0001 };
        const ENC  = { WPA2: 0x0008, WPA_WPA2: 0x000C, OPEN: 0x0001 };
        const auth = AUTH[p.auth] ?? 0x0020;
        const enc  = ENC[p.auth] ?? 0x0008;
        const te = new TextEncoder();

        const u16 = (n) => new Uint8Array([(n >> 8) & 0xff, n & 0xff]);
        const tlv = (type, val) => {
            const out = new Uint8Array(4 + val.length);
            out.set(u16(type), 0);
            out.set(u16(val.length), 2);
            out.set(val, 4);
            return out;
        };
        const cat = (arr) => {
            const len = arr.reduce((n, a) => n + a.length, 0);
            const out = new Uint8Array(len);
            let o = 0;
            for (const a of arr) { out.set(a, o); o += a.length; }
            return out;
        };

        const attrs = [
            tlv(0x1026, new Uint8Array([0x01])),            // Network Index
            tlv(0x1045, te.encode(p.ssid || '')),           // SSID
            tlv(0x1003, u16(auth)),                         // Authentication Type
            tlv(0x100F, u16(enc)),                          // Encryption Type
            tlv(0x1027, te.encode(p.auth === 'OPEN' ? '' : (p.password || ''))), // Network Key
            tlv(0x1020, new Uint8Array([0, 0, 0, 0, 0, 0])),// MAC Address
        ];
        return tlv(0x100E, cat(attrs)); // Credential
    }

    /* ---------- Byte estimate (NDEF message on tag) ------------------ */
    function estimateBytes(p) {
        if (p.kind === 'wifi') {
            const wsc = buildWsc(p).length;
            const typeLen = 'application/vnd.wfa.wsc'.length; // 23
            const plLenBytes = wsc > 255 ? 4 : 1;
            const ndef = 1 /*hdr*/ + 1 /*typeLen*/ + plLenBytes + typeLen + wsc;
            return ndef + 3; // tag NDEF TLV wrapper (0x03 len … 0xFE)
        }
        // URL record with scheme abbreviation.
        const url = p.url || '';
        const abbr = ['https://www.', 'http://www.', 'https://', 'http://'];
        let rest = url;
        for (const a of abbr) { if (url.startsWith(a)) { rest = url.slice(a.length); break; } }
        const payloadLen = 1 /*abbrev id*/ + new TextEncoder().encode(rest).length;
        const plLenBytes = payloadLen > 255 ? 4 : 1;
        const ndef = 1 /*hdr*/ + 1 /*typeLen*/ + plLenBytes + 1 /*type 'U'*/ + payloadLen;
        return ndef + 3;
    }

    /* ---------- helpers ---------------------------------------------- */
    function guard() {
        if (!supported) { window.App.toast('Web NFC no disponible en este navegador.', 'error'); return false; }
        if (busy) return false;
        busy = true;
        return true;
    }
    function setStatus(html, isErr) {
        const el = $('nfcStatus');
        el.innerHTML = html;
        el.style.color = isErr ? 'var(--danger)' : '';
    }
    function fail(e) {
        const map = {
            NotAllowedError: 'Permiso denegado. Activa NFC y permite el acceso.',
            NotSupportedError: 'NFC no disponible en este dispositivo.',
            NotReadableError: 'No se pudo acceder al NFC. ¿Está activado?',
            AbortError: 'Operación cancelada.',
        };
        setStatus('<i class="fa-solid fa-circle-exclamation me-1"></i> ' + (map[e.name] || e.message || 'Error de NFC.'), true);
    }
    function esc(s) {
        return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    /* ---------- wire up ---------------------------------------------- */
    $('btnValidate').addEventListener('click', validate);
    $('btnWrite').addEventListener('click', write);
    ['fltTenant', 'fltEncoded'].forEach((id) => $(id).addEventListener('change', loadQueue));
    let qT;
    $('fltQ').addEventListener('input', () => { clearTimeout(qT); qT = setTimeout(loadQueue, 300); });

    loadQueue();
})();
</script>
<style>
    .nfc-row { display:flex; align-items:center; gap:.65rem; width:100%; text-align:left;
        padding:.6rem .9rem; background:transparent; border:0; border-bottom:1px solid var(--border);
        cursor:pointer; transition:var(--t-fast); }
    .nfc-row:hover { background:var(--accent-soft); }
    .nfc-row-active { background:var(--accent-soft); box-shadow:inset 3px 0 0 var(--accent); }
    .nfc-row-main { flex-grow:1; min-width:0; display:flex; flex-direction:column; }
    .nfc-row-main code { font-size:.9rem; }
    .nfc-row-sub { font-size:.78rem; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .nfc-row-type { font-size:.74rem; white-space:nowrap; }
    .nfc-result { border:1px solid var(--border); }
    .nfc-result-empty  { border-color:var(--accent); background:var(--accent-soft); }
    .nfc-result-ours   { border-color:var(--success); }
    .nfc-result-foreign{ border-color:var(--warning); }
    .spinner-sm { width:20px; height:20px; border:2px solid var(--border); border-top-color:var(--accent);
        border-radius:50%; animation:nfcspin .7s linear infinite; }
    @keyframes nfcspin { to { transform:rotate(360deg); } }
</style>
@endpush
@endsection
