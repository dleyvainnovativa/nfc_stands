@extends('layouts.app')
@section('title', 'Panel · NFC Stands')
@section('page-title', 'Panel')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1">Resumen</h1>
        <p class="text-muted-2 mb-0" style="font-size:.92rem">
            Alcance: <span class="fw-semibold">{{ $scope }}</span>
        </p>
    </div>
    @unless($platformView)
        <a href="{{ route('stands.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Nuevo stand
        </a>
    @endunless
</div>

<div class="row g-3 mb-4">
    @foreach ($stats as $s)
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label">{{ $s['label'] }}</div>
                        <div class="stat-value">{{ $s['value'] }}</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid {{ $s['icon'] }}"></i></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body d-flex align-items-center gap-3 flex-wrap">
        <div class="stat-icon"><i class="fa-solid fa-wifi"></i></div>
        <div class="flex-grow-1">
            <div class="fw-semibold">Administra tus stands</div>
            <div class="text-muted-2" style="font-size:.9rem">
                Crea stands, define a dónde apunta cada etiqueta y observa los escaneos en tiempo real.
            </div>
        </div>
        <a href="{{ route('stands.index') }}" class="btn btn-outline-secondary btn-sm">Ir a stands</a>
    </div>
</div>
@endsection
