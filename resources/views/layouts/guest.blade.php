<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
    @include('partials.head')
</head>
<body>
    <div class="auth-wrap">
        @yield('content')
    </div>

    @include('partials.flash')
    @stack('scripts')
</body>
</html>
