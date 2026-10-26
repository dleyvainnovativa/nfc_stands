<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.theme-init')
<title>@yield('title', 'NFC Stands')</title>

{{-- Fonts: Inter + JetBrains Mono --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;650;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

{{-- Font Awesome (free) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

{{-- App bundle (Bootstrap JS + theme.css + helpers) --}}
@vite(['resources/css/main.css', 'resources/js/app.js'])