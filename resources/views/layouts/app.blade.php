<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
    @include('partials.head')
</head>
<body>
    <div class="app-shell">
        @include('partials.sidebar')

        <div class="main">
            @include('partials.topbar')

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.flash')
    @stack('scripts')
</body>
</html>
