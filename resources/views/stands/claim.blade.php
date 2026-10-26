@extends('layouts.app')
@section('title', 'Reclamar etiqueta · NFC Stands')
@section('page-title', 'Reclamar etiqueta')

@section('content')
<div class="card" style="max-width:480px">
    <div class="card-body">
        <h1 class="h5 fw-bold mb-1">Reclamar etiqueta</h1>
        <p class="text-muted-2 mb-4" style="font-size:.9rem">
            Ingresa el código impreso en una etiqueta NFC en blanco para vincularla a tu cuenta.
        </p>
        <form method="POST" action="{{ route('stands.claim.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Código de etiqueta</label>
                <input type="text" name="code" value="{{ old('code') }}"
                       class="form-control mono @error('code') is-invalid @enderror"
                       placeholder="e.g. a9Kd2PqX" autofocus required>
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button class="btn btn-primary w-100">Reclamar etiqueta</button>
        </form>
    </div>
</div>
@endsection
