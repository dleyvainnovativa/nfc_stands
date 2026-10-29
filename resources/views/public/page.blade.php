<!DOCTYPE html>
<html style="background-color:{{ $page->brandPrimary() }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="{{ $page->chromeColor() }}">
    <title>{{ $page->displayTitle() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if ($fontUrl = \App\Support\GoogleFont::cssUrl($page->font_family))
        <link href="{{ $fontUrl }}" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html { min-height: 100%; }
        /* Background lives on <body> (not .wrap) and bleeds into the iOS safe
           areas, so the status bar and bottom toolbar fill with the brand
           colour instead of white. */
        body { margin: 0; min-height: 100vh; font-family: var(--font, Inter, system-ui, -apple-system, sans-serif); }
        body.bg-gradient { background: linear-gradient(160deg, var(--pp), var(--ps)); }
        body.bg-plain { background: var(--bgc); }
        .wrap {
            min-height: 100vh; display: flex; flex-direction: column; align-items: center;
            padding: calc(3rem + env(safe-area-inset-top, 0px)) 1.25rem calc(1.25rem + env(safe-area-inset-bottom, 0px));
            color: var(--txt);
        }
        .inner { width: 100%; max-width: 460px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 1rem; }
        .logo img { max-height: 84px; max-width: 220px; object-fit: contain; }
        h1 { font-size: 1.6rem; margin: .25rem 0 0; line-height: 1.2; }
        .desc { opacity: .9; margin: 0; font-size: .95rem; }
        .links { width: 100%; display: flex; flex-direction: column; gap: .7rem; margin-top: .75rem; }
        .plink {
            display: flex; align-items: center; justify-content: center; gap: .6rem;
            padding: .95rem 1rem; font-weight: 600; text-decoration: none;
            transition: transform .08s ease, filter .15s ease;
        }
        .plink:hover { transform: translateY(-1px); filter: brightness(1.04); }
        .plink i { font-size: 1.05rem; }
        .shape-rounded { border-radius: 12px; } .shape-pill { border-radius: 999px; } .shape-square { border-radius: 4px; }
        .plink.fill-solid { background: var(--pp); color: var(--onp); box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        .plink.fill-outline { background: transparent; border: 2px solid var(--txt); color: var(--txt); }
        .empty { opacity: .8; font-size: .95rem; }
        .site-footer { margin-top: auto; padding-top: 1rem; padding-bottom: 1rem; text-align: center; font-size: .78rem; opacity: .7; }
        .site-footer a { color: inherit; text-decoration: none; }
        .site-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body class="bg-{{ $page->bg_style }}"
      style="--pp:{{ $page->brandPrimary() }};--ps:{{ $page->brandSecondary() }};--bgc:{{ $page->bgColor() }};--txt:{{ $page->textColor() }};--onp:{{ $page->onPrimary() }};--font:{{ \App\Support\GoogleFont::stack($page->font_family) }}">
<div class="wrap">
    <div class="inner">
        @if ($page->logoUrl())
            <div class="logo"><img src="{{ $page->logoUrl() }}" alt="{{ $page->name }}"></div>
        @endif
        <h1>{{ $page->displayTitle() }}</h1>
        @if ($page->description)
            <p class="desc">{{ $page->description }}</p>
        @endif

        <div class="links">
            @forelse ($page->cleanLinks() as $i => $link)
                @php($lbg = $link['bg'] ?? null)
                <a class="plink shape-{{ $page->button_shape }} {{ $lbg ? '' : 'fill-' . $page->button_fill }}"
                   @if ($lbg) style="background:{{ $lbg }};color:{{ $link['text'] ?: '#ffffff' }};border:none;box-shadow:0 2px 8px rgba(0,0,0,.12);" @endif
                   href="{{ route('page.link', ['code' => $page->code, 'i' => $i]) }}" rel="noopener">
                    @if (!empty($link['icon']))<i class="{{ $link['icon'] }}"></i>@endif
                    <span>{{ $link['label'] }}</span>
                </a>
            @empty
                <p class="empty">Aún no hay enlaces.</p>
            @endforelse
        </div>
    </div>

    <footer class="site-footer">
        <a href="{{ url('/') }}">Desarrollado por NFC</a>
    </footer>
</div>
</body>
</html>
