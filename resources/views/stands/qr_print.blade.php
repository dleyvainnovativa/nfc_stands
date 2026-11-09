<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR · {{ $stand->label ?: $stand->code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        :root { --text:#1a1d23; --muted:#667085; --border:#e4e7ec; --accent:#1150ed; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:"Hanken Grotesk",system-ui,sans-serif; color:var(--text);
               background:#f7f8fa; display:grid; place-items:center; min-height:100vh; padding:2rem; }
        .sheet { background:#fff; border:1px solid var(--border); border-radius:14px;
                 padding:2.5rem; text-align:center; max-width:420px; width:100%; }
        /* width:100% (not a fixed 320px) so the card can't force the grid track
           wider than the viewport on small screens. */
        .qr { width:100%; max-width:320px; margin:0 auto 1.25rem; }
        .qr svg { width:100%; height:auto; display:block; }
        h1 { font-size:1.25rem; margin:0 0 .25rem; }
        .code { font-family:'JetBrains Mono',monospace; font-size:1.1rem; letter-spacing:.04em; }
        .url { color:var(--muted); font-size:.85rem; margin-top:.5rem; word-break:break-all; }
        .hint { color:var(--muted); font-size:.95rem; margin:.75rem 0 0; }
        .actions { margin-top:1.5rem; display:flex; flex-wrap:wrap; gap:.6rem; justify-content:center; }
        button, a.btn { font:inherit; font-weight:600; border-radius:8px; padding:.6rem 1.1rem;
               border:1px solid var(--border); background:#fff; color:var(--text); cursor:pointer; text-decoration:none; }
        button.primary { background:var(--accent); border-color:var(--accent); color:#fff; }
        @media (max-width:480px) {
            body { padding:1rem; }
            .sheet { padding:1.5rem; border-radius:12px; }
        }
        @media print {
            body { background:#fff; padding:0; }
            .sheet { border:none; box-shadow:none; }
            .actions { display:none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="qr">{!! $svg !!}</div>
        <h1>{{ $stand->label ?: 'Escanéame' }}</h1>
        <div class="code">{{ $stand->code }}</div>
        <p class="hint">Escanea o acerca tu teléfono para abrir</p>
        <div class="url">{{ $stand->publicUrl() }}</div>
        <div class="actions">
            <button class="primary" onclick="window.print()">Imprimir</button>
            <a class="btn" href="{{ route('stands.qr', $stand) }}?dl=1">Descargar SVG</a>
        </div>
    </div>
</body>
</html>
