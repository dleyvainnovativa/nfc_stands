<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->displayTitle() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, system-ui, -apple-system, sans-serif; }
        .wrap {
            min-height: 100vh; display: flex; flex-direction: column; align-items: center;
            padding: 3rem 1.25rem calc(3rem + env(safe-area-inset-bottom, 0px));
        }
        .wrap.bg-gradient { background: linear-gradient(160deg, var(--pp), var(--ps)); color: #fff; }
        .wrap.bg-plain { background: #f4f5f7; color: #1e2733; }
        .inner { width: 100%; max-width: 460px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 1rem; }
        .logo img { max-height: 84px; max-width: 220px; object-fit: contain; }
        h1 { font-size: 1.6rem; margin: .25rem 0 0; line-height: 1.2; }
        .desc { opacity: .9; margin: 0; font-size: .95rem; }
        .links { width: 100%; display: flex; flex-direction: column; gap: .7rem; margin-top: .75rem; }
        .plink {
            display: flex; align-items: center; justify-content: center; gap: .6rem;
            padding: .95rem 1rem; font-weight: 600; text-decoration: none;
            transition: transform .08s ease, filter .15s ease; box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        .plink:hover { transform: translateY(-1px); filter: brightness(1.03); }
        .plink i { font-size: 1.05rem; }
        .shape-rounded { border-radius: 12px; } .shape-pill { border-radius: 999px; } .shape-square { border-radius: 4px; }
        .bg-gradient .fill-solid { background: #fff; color: var(--pp); }
        .bg-gradient .fill-outline { background: transparent; border: 2px solid rgba(255,255,255,.85); color: #fff; box-shadow: none; }
        .bg-plain .fill-solid { background: var(--pp); color: #fff; }
        .bg-plain .fill-outline { background: transparent; border: 2px solid var(--pp); color: var(--pp); box-shadow: none; }
        .empty { opacity: .8; font-size: .95rem; }
    </style>
</head>
<body>
<div class="wrap bg-{{ $page->bg_style }}" style="--pp:{{ $page->brandPrimary() }};--ps:{{ $page->brandSecondary() }}">
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
                <a class="plink shape-{{ $page->button_shape }} fill-{{ $page->button_fill }}"
                   href="{{ route('page.link', ['code' => $page->code, 'i' => $i]) }}" rel="noopener">
                    @if (!empty($link['icon']))<i class="{{ $link['icon'] }}"></i>@endif
                    <span>{{ $link['label'] }}</span>
                </a>
            @empty
                <p class="empty">Aún no hay enlaces.</p>
            @endforelse
        </div>
    </div>
</div>
</body>
</html>
