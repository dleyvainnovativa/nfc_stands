<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme-init')
    <title>{{ $stand->destination_config['title'] ?? ($stand->label ?: 'Links') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/main.css', 'resources/js/app.js'])
</head>
<body>
    <div style="min-height:100vh;display:grid;place-items:start center;padding:2.5rem 1rem;background:
        radial-gradient(900px 400px at 50% -10%, var(--accent-soft), transparent 60%), var(--bg);">
        <div style="width:100%;max-width:440px">
            <div style="text-align:center;margin-bottom:1.75rem">
                <div class="logo-mark" style="width:52px;height:52px;margin:0 auto 1rem;font-size:1.3rem">
                    <i class="fa-solid fa-wifi"></i>
                </div>
                <h1 class="h4 fw-bold mb-0">{{ $stand->destination_config['title'] ?? ($stand->label ?: 'Bienvenido') }}</h1>
            </div>

            <div class="d-flex flex-column gap-2">
                @forelse (($stand->destination_config['links'] ?? []) as $i => $link)
                    {{-- Route through the tracker so we learn which link is tapped --}}
                    <a href="{{ route('stand.link', ['code' => $stand->code, 'i' => $i]) }}" rel="noopener"
                       class="card"
                       style="display:flex;align-items:center;justify-content:center;gap:.5rem;
                              padding:.95rem 1rem;font-weight:600;color:var(--text);text-decoration:none">
                        {{ $link['label'] }}
                    </a>
                @empty
                    <p class="text-center text-muted-2">Aún no hay enlaces configurados.</p>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
