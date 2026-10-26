<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme-init')
    <title>No disponible</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/main.css', 'resources/js/app.js'])
</head>
<body>
    <div style="min-height:100vh;display:grid;place-items:center;padding:1.5rem;background:var(--bg)">
        <div class="card" style="max-width:420px;width:100%;text-align:center">
            <div class="card-body p-5">
                @php($icon = $reason === 'unassigned' ? 'fa-tag' : 'fa-link-slash')
                <div class="stat-icon mx-auto mb-3" style="width:52px;height:52px;font-size:1.2rem">
                    <i class="fa-solid {{ $icon }}"></i>
                </div>
                @if ($reason === 'not_found')
                    <h1 class="h5 fw-bold mb-1">Etiqueta no encontrada</h1>
                    <p class="text-muted-2 mb-0">Este código no corresponde a ningún stand.</p>
                @elseif ($reason === 'unassigned')
                    <h1 class="h5 fw-bold mb-1">Aún no configurado</h1>
                    <p class="text-muted-2 mb-0">Esta etiqueta aún no se ha configurado. Vuelve pronto.</p>
                @else
                    <h1 class="h5 fw-bold mb-1">No disponible por ahora</h1>
                    <p class="text-muted-2 mb-0">Este stand está desactivado en este momento.</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
