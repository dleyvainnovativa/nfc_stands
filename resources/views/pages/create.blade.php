@extends('layouts.app')
@section('title', 'Nueva página · NFC Stands')
@section('page-title', 'Nueva página')

@section('content')
<div class="mb-3">
    <a href="{{ route('pages.index') }}" class="text-muted-2" style="font-size:.9rem">
        <i class="fa-solid fa-arrow-left me-1"></i> Páginas
    </a>
</div>

<div class="card" style="max-width:520px">
    <div class="card-body">
        <h1 class="h5 fw-bold mb-1">Crear página</h1>
        <p class="text-muted-2 mb-4" style="font-size:.9rem">
            Dale un nombre; el cliente edita su contenido y tú la publicas cuando esté lista.
        </p>
        <form method="POST" action="{{ route('pages.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-control @error('name') is-invalid @enderror" maxlength="160" required autofocus>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Crear página</button>
                <a href="{{ route('pages.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
