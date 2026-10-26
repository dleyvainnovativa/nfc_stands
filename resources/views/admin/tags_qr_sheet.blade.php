<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hoja de QR · etiquetas en blanco</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        body { margin:0; font-family:Inter,system-ui,sans-serif; color:#1a1d23; background:#f7f8fa; padding:1.5rem; }
        .bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; max-width:1000px; margin-inline:auto; }
        .bar h1 { font-size:1.1rem; margin:0; }
        .grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:.75rem; max-width:1000px; margin:0 auto; }
        .cell { background:#fff; border:1px solid #e4e7ec; border-radius:10px; padding:.75rem; text-align:center; }
        .cell svg { width:100%; height:auto; display:block; }
        .cell .code { font-family:'JetBrains Mono',monospace; font-size:.85rem; margin-top:.4rem; letter-spacing:.03em; }
        button { font:inherit; font-weight:600; border-radius:8px; padding:.55rem 1rem; border:1px solid #2e6be6;
                 background:#2e6be6; color:#fff; cursor:pointer; }
        .empty { text-align:center; color:#667085; padding:3rem; }
        @media print {
            body { background:#fff; padding:0; }
            .bar { display:none; }
            .grid { gap:.4rem; grid-template-columns:repeat(5, 1fr); }
            .cell { border-color:#ccc; break-inside:avoid; }
        }
    </style>
</head>
<body>
    <div class="bar">
        <h1>Etiquetas en blanco — {{ $cells->count() }} en esta hoja</h1>
        <button onclick="window.print()">Imprimir</button>
    </div>

    @if ($cells->isEmpty())
        <div class="empty">No hay etiquetas en blanco para imprimir. Genera un lote primero.</div>
    @else
        <div class="grid">
            @foreach ($cells as $c)
                <div class="cell">
                    {!! $c['svg'] !!}
                    <div class="code">{{ $c['code'] }}</div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
