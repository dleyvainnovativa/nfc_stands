<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme-init')
    <title>Gracias</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/main.css', 'resources/js/app.js'])
    @include('partials.brand-style')
    <style>
        .survey-toggle { position:fixed; top:.75rem; right:.75rem; z-index:10;
            background:var(--surface); border:1px solid var(--border); }
    </style>
</head>
<body>
<button class="icon-btn survey-toggle" data-theme-toggle type="button" aria-label="Cambiar tema" title="Cambiar tema">
    <i class="fa-solid fa-circle-half-stroke"></i>
</button>
<div style="min-height:100vh;display:grid;place-items:center;padding:1.5rem;background:
    radial-gradient(900px 400px at 50% -10%, var(--accent-soft), transparent 60%), var(--bg);">
    <div class="card" style="max-width:420px;width:100%;text-align:center">
        <div class="card-body p-5">
            @if (isset($brand) && $brand && $brand->logoUrl())
                <div style="margin-bottom:1.25rem"><img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }}" style="max-height:52px;max-width:180px;object-fit:contain"></div>
            @endif
            <div class="mx-auto mb-3" style="width:56px;height:56px;border-radius:999px;display:grid;place-items:center;background:var(--success-soft);color:var(--success)">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </div>
            <h1 class="h5 fw-bold mb-1">¡Gracias!</h1>
            <p class="text-muted-2 mb-0">
                {{ $survey->thank_you_message ?: 'Tu respuesta ha sido registrada.' }}
            </p>
            @if ($survey->return_url)
                <div class="mt-4">
                    <a href="{{ $survey->return_url }}" rel="noopener" class="btn btn-brand w-100">
                        {{ $survey->return_label ?: 'Volver' }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
