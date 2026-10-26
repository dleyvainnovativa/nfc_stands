@extends('layouts.guest')
@section('title', 'Iniciar sesión · NFC Stands')

@section('content')
<div class="auth-split">
    {{-- Brand panel (desktop) --}}
    <aside class="auth-brand">
        <div>
            <span class="brand-mark"><i class="fa-solid fa-wifi"></i></span>
            <div class="brand-name">NFC Stands</div>
            <div class="brand-tagline">Tus stands NFC, en un solo lugar.</div>
        </div>

        <ul class="brand-points">
            <li><i class="fa-solid fa-arrows-rotate"></i> Cambia el destino de cada etiqueta sin reimprimir</li>
            <li><i class="fa-solid fa-chart-line"></i> Escaneos, encuestas y reseñas en tiempo real</li>
            <li><i class="fa-solid fa-store"></i> Menú, redes sociales y reseñas de Google</li>
        </ul>
    </aside>

    {{-- Form panel --}}
    <div class="auth-form">
        <div class="mobile-brand">
            <span class="logo-mark"><i class="fa-solid fa-wifi"></i></span>
            <span>NFC Stands</span>
        </div>

        <h1 class="h4 fw-bold mb-1">Iniciar sesión</h1>
        <p class="text-muted-2 mb-4" style="font-size:.92rem">
            Accede a tu panel de control
        </p>

        <form method="POST" action="{{ route('login.store') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       autocomplete="email" autofocus required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       autocomplete="current-password" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label text-muted-2" for="remember" style="font-size:.88rem">
                    Mantener sesión iniciada
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Entrar
            </button>
        </form>
    </div>
</div>
@endsection
